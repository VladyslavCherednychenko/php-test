FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    libpq-dev \
    icu-dev \
    git \
    unzip \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    intl \
    opcache \
    && apk add --no-cache --virtual .build-deps \
    autoconf \
    g++ \
    make \
    linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .build-deps

COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY backend/composer.* ./
RUN composer install --no-interaction --no-scripts --no-autoloader

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

COPY backend/ .
RUN composer dump-autoload --optimize

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]
