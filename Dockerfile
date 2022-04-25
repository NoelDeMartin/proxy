FROM php:8.1-fpm-alpine

# Install PHP Dependencies
RUN set -x \
    && apk update \
    && apk add zlib-dev libzip-dev \
    && docker-php-ext-install zip bcmath \
    && docker-php-ext-enable bcmath \
    && php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer \
    && composer self-update --1

# Prepare Supervisor
RUN apk add --no-cache supervisor
COPY supervisord.conf /etc/supervisord.conf

# Prepare app
WORKDIR /app
COPY . /app
RUN composer install --no-dev
RUN mkdir /app/storage \
    && mkdir /app/storage/logs \
    && mkdir /app/storage/framework \
    && mkdir /app/storage/framework/sessions \
    && mkdir /app/storage/framework/cache \
    && mkdir /app/storage/framework/views \
    && chown www-data:www-data /app/storage -R

# Default command
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
