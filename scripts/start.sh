#!/usr/bin/env bash

if [[ $(type -t proxy-cli) != function ]]; then
    echo "Don't call scripts directly, use the proxy binary!"

    exit;
fi

proxy-docker-compose up -d

if proxy_is_headless; then
    proxy-cli publish-assets
fi
