#!/usr/bin/env bash

if [ -f .env ]; then
    echo "Already installed!"
    exit;
fi

cp .env.prod.example .env
./set-permissions.sh
docker-compose -f docker-compose.prod.yml run app php artisan key:generate
docker-compose -f docker-compose.prod.yml run app php artisan view:clear
docker-compose -f docker-compose.prod.yml run app php artisan config:cache
docker-compose -f docker-compose.prod.yml run app php artisan route:cache

echo "Installed successfully, before starting the application you may want to take a look at your .env file."
