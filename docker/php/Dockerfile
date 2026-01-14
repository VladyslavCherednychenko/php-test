FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    libpq-dev \
    icu-dev \
    git \
    unzip

RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    intl \
    opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html