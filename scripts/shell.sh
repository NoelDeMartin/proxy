#!/usr/bin/env bash

if ! proxy_is_running; then
    echo "App is not running!"

    exit
fi

service=${1:-app}

proxy-docker-compose exec $service sh
