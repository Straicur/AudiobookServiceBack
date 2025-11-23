# syntax=docker/dockerfile:1
FROM php:8.2-fpm AS base

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libcurl4-openssl-dev \
    libgmp-dev \
    libpq-dev \
    && docker-php-ext-install -j$(nproc) intl pdo_mysql zip curl gmp \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN usermod -u 1000 www-data

# Dev/Test Stage (opcjonalny)
FROM base AS dev

# Prod Stage
FROM base AS prod
ENV APP_ENV=prod

COPY --link composer.* symfony.* ./
RUN set -eux; \
    composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

COPY --link . /var/www/html/

RUN set -eux; \
    composer dump-autoload --classmap-authoritative --no-dev; \
    composer dump-env prod; \
    composer run-script --no-dev post-install-cmd; \
    chown -R www-data:www-data /var/www/html; \
    chmod -R 777 /var/www/html/var; sync;

USER www-data

CMD ["php-fpm"]