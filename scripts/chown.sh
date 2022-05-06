#!/usr/bin/env bash

if [[ $(type -t proxy-cli) != function ]]; then
    echo "Don't call scripts directly, use the proxy binary!"

    exit;
fi

WWWDATA_UID=`docker-compose -f docker-compose.prod.yml run app id -u www-data | tail -n 1 | sed 's/\r$//'`

if [ -z $WWWDATA_UID ]; then
    echo "Could not set permissions"

    exit 1
fi

sudo chown -R $WWWDATA_UID:docker storage
