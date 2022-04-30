#!/usr/bin/env bash

is_running=$(proxy-docker-compose ps --quiet | head -n 1)

# Abort on errors
set -e

# Update
git pull
proxy-docker-compose build

if [ -z $is_running ]; then
    proxy-docker-compose run app php artisan view:clear
    proxy-docker-compose run app php artisan config:cache
    proxy-docker-compose run app php artisan route:cache
else
    proxy-cli restart
    proxy-docker-compose exec app php artisan view:clear
    proxy-docker-compose exec app php artisan config:cache
    proxy-docker-compose exec app php artisan route:cache
fi

echo "Updated successfully!"
