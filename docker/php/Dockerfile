FROM php:8.2-fpm-alpine

ARG PUID=1000
ARG PGID=1000

RUN set -eux; \
    deluser www-data || true; \
    delgroup www-data || true; \
    addgroup -g "${PGID}" www-data; \
    adduser  -D -H -u "${PUID}" -G www-data -s /sbin/nologin www-data

USER root

COPY composer.json composer.lock ./

RUN apk add --no-cache \
        autoconf \
        g++ \
        make \
        icu-dev \
        zlib-dev \
        libzip-dev \
        bash \
        git \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        intl \
        opcache \
        pdo \
        pdo_mysql \
        mysqli \
        zip

RUN apk add --no-cache $PHPIZE_DEPS linux-headers \
    && pecl install xdebug-3.3.2 \
    && docker-php-ext-enable xdebug

COPY docker/php/conf.d/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

USER www-data
WORKDIR /var/www/html
