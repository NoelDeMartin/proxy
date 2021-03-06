#!/usr/bin/env bash

if [[ $(type -t proxy-cli) != function ]]; then
    echo "Don't call scripts directly, use the proxy binary!"

    exit;
fi

if ! proxy_is_running; then
    echo "App is not running!"

    exit
fi

service=${1:-app}

proxy-docker-compose exec $service sh
