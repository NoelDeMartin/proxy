#!/usr/bin/env bash

# Abort on errors
set -e

# Update
git pull
proxy-docker-compose build

if proxy_is_running; then
    proxy-cli restart
    proxy-docker-compose exec app php artisan config:cache
    proxy-docker-compose exec app php artisan event:cache
    proxy-docker-compose exec app php artisan optimize
    proxy-docker-compose exec app php artisan route:cache
    proxy-docker-compose exec app php artisan view:cache
else
    proxy-docker-compose run app php artisan config:cache
    proxy-docker-compose run app php artisan event:cache
    proxy-docker-compose run app php artisan optimize
    proxy-docker-compose run app php artisan route:cache
    proxy-docker-compose run app php artisan view:cache
fi

echo "Updated successfully!"
