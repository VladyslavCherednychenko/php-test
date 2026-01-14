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

COPY backend/composer.* ./

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

COPY backend/ .

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]