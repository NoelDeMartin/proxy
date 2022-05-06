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
    echo "What origins should the proxy be restricted to? Provide a comma-separated list of domains."
    read CORS_ORIGINS
}

function ask_nginx_agora() {
    while
        echo "Do you want to use nginx-agora? (y/n)"
        read USE_NGINX_AGORA

        if [[ $USE_NGINX_AGORA != y && $USE_NGINX_AGORA != n ]]; then
            echo "Please, respond with 'y' or 'n'."

            continue
        fi

        break
    do true; done
}

# Check if installing is necessary
if [ -f "$base_dir/.env" ]; then
    echo "Already installed!"
    exit
fi

# Abort and clean up on error
trap "clean_up" ERR

function clean_up() {
    if [ -d "$base_dir/nginx-agora" ]; then
        rm $base_dir/nginx-agora -rf
    fi

    if [ -f "$base_dir/.env" ]; then
        rm $base_dir/.env
    fi

    # TODO uninstall site from nginx-agora if installed

    exit
}

# Prepare .env
ask_url
ask_cors_origins
ask_nginx_agora

ESCAPED_APP_URL=$(printf '%s\n' "$APP_URL" | sed -e 's/[\/&]/\\&/g')
ESCAPED_CORS_ORIGINS=$(printf '%s\n' "$CORS_ORIGINS" | sed -e 's/[\/&]/\\&/g')

cp .env.prod.example .env
sed s/APP_URL=/APP_URL=$ESCAPED_APP_URL/g -i .env
sed s/CORS_ORIGINS=/CORS_ORIGINS=$ESCAPED_CORS_ORIGINS/g -i .env

# Prepare nginx-agora
if [[ $USE_NGINX_AGORA == y ]]; then
    APP_DOMAIN=$(echo $APP_URL | sed -E s/https?:\\/\\///g)

    mkdir "$base_dir/nginx-agora"
    cp "$base_dir/docker/nginx/proxy.conf.template" "$base_dir/nginx-agora/$APP_DOMAIN.conf"
    sed "s/root \\/var\\/www\\/html/root \\/var\\/www\\/proxy/g" -i "$base_dir/nginx-agora/$APP_DOMAIN.conf"
    sed "s/fastcgi_pass app:9000/fastcgi_pass proxy:9000/g" -i "$base_dir/nginx-agora/$APP_DOMAIN.conf"
    sed s/\\[\\[APP_DOMAIN\\]\\]/$APP_DOMAIN/g -i "$base_dir/nginx-agora/$APP_DOMAIN.conf"

    nginx-agora install "$base_dir/nginx-agora/$APP_DOMAIN.conf" "$base_dir/public" proxy
fi

# Prepare assets
if [[ proxy_is_headless && ! -d "$base_dir/public" ]]; then
    mkdir "$base_dir/public"
fi

# Prepare storage
proxy-cli chown
proxy-docker-compose run app php artisan key:generate
proxy-docker-compose run app php artisan config:cache
proxy-docker-compose run app php artisan event:cache
proxy-docker-compose run app php artisan optimize
proxy-docker-compose run app php artisan route:cache
proxy-docker-compose run app php artisan view:cache

echo "Installed successfully!"
