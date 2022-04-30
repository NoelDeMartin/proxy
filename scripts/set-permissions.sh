#!/usr/bin/env bash

WWWDATA_UID=`docker-compose -f docker-compose.prod.yml run app id -u www-data | sed 's/\r$//'`

sudo chown -R $WWWDATA_UID:docker storage
