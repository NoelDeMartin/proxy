#!/usr/bin/env bash

if [[ $(type -t proxy-cli) != function ]]; then
    echo "Don't call scripts directly, use the proxy binary!"

    exit;
fi

if ! proxy_is_headless; then
    echo "Assets should only be published in headless mode!"

    exit
fi

if ! proxy_is_running; then
    echo "Can't publish assets if app is not running!"

    exit
fi

containerid=`proxy-docker-compose ps --quiet app`
containername=`docker ps --filter "id=$containerid" --format="{{.Names}}"`

rm $base_dir/public/* -rf
docker cp "$containername:/app/public/." "$base_dir/public"
