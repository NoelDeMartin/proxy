#!/usr/bin/env bash

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

# Install
cp .env.prod.example .env

proxy-cli set-permissions
proxy-docker-compose run app php artisan key:generate
proxy-docker-compose run app php artisan view:clear
proxy-docker-compose run app php artisan config:cache
proxy-docker-compose run app php artisan route:cache

echo "Installed successfully, before starting the application you may want to take a look at your .env file."
