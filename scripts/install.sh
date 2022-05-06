#!/usr/bin/env bash

if [[ $(type -t proxy-cli) != function ]]; then
    echo "Don't call scripts directly, use the proxy binary!"

    exit;
fi

function ask_url() {
    while
        echo "What's the base url of your application?"
        read APP_URL

        if [ -z $APP_URL ]; then
            echo "The url cannot be empty"

            continue
        fi

        if [[ $APP_URL != http://* && $APP_URL != https://* ]]; then
            echo "The url should start with http:// or https://"

            continue
        fi

        break
    do true; done
}

function ask_cors_origins() {
    echo "What domains should the proxy be restricted to? Provide a comma-separated list of domains."
    read CORS_ORIGINS
}

# Check if installing is necessary
if [ -f "$base_dir/.env" ]; then
    echo "Already installed!"
    exit
fi

# Abort and clean up on error
trap "clean_up" ERR

function clean_up() {
    if [ -f "$base_dir/.env" ]; then
        rm $base_dir/.env
    fi

    exit
}

# Prepare .env
ask_url
ask_cors_origins

ESCAPED_APP_URL=$(printf '%s\n' "$APP_URL" | sed -e 's/[\/&]/\\&/g')
ESCAPED_CORS_ORIGINS=$(printf '%s\n' "$CORS_ORIGINS" | sed -e 's/[\/&]/\\&/g')

cp .env.prod.example .env
sed s/APP_URL=/APP_URL=$ESCAPED_APP_URL/g -i .env
sed s/CORS_ORIGINS=/CORS_ORIGINS=$ESCAPED_CORS_ORIGINS/g -i .env

# Prepare storage
proxy-cli chown
proxy-docker-compose run app php artisan key:generate
proxy-docker-compose run app php artisan config:cache
proxy-docker-compose run app php artisan event:cache
proxy-docker-compose run app php artisan optimize
proxy-docker-compose run app php artisan route:cache
proxy-docker-compose run app php artisan view:cache

echo "Installed successfully!"
