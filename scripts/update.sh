#!/usr/bin/env bash

if [[ $(type -t proxy-cli) != function ]]; then
    echo "Don't call scripts directly, use the proxy binary!"

    exit;
fi

# Abort on errors
set -e

# Pull new code
git -C $base_dir pull

# Update nginx-agora
# TODO if nginx-agora is configured, regenerate and copy nginx config

# Update containers
if proxy_is_headless; then
    proxy-docker-compose pull
else
    proxy-docker-compose build
fi

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
