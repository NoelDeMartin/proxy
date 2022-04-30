#!/usr/bin/env bash

git pull
docker-compose -f docker-compose.prod.yml build
./restart.sh
docker-compose -f docker-compose.prod.yml exec app php artisan view:clear
docker-compose -f docker-compose.prod.yml exec app php artisan config:cache
docker-compose -f docker-compose.prod.yml exec app php artisan route:cache

echo "Updated successfully!"
