# syntax=docker/dockerfile:1
FROM php:8.4-fpm AS base

ARG MAIN_DIR

WORKDIR /var/www/html

# Instalacja rozszerzeń
RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libzip-dev libicu-dev libcurl4-openssl-dev libgmp-dev libpq-dev \
    && docker-php-ext-install -j$(nproc) intl pdo_mysql zip curl gmp \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN echo "date.timezone = UTC" > /usr/local/etc/php/conf.d/timezone.ini
COPY docker/php/php.ini /usr/local/etc/php/conf.d/php.ini
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN usermod -u 1000 www-data

# Etap deweloperski
FROM base AS dev
ENV APP_ENV=dev
# Tworzymy MAIN_DIR dla dev
RUN mkdir -p ${MAIN_DIR} && chown www-data:www-data ${MAIN_DIR}
USER www-data

# Etap testowy
FROM base AS test
ENV APP_ENV=test

RUN echo "memory_limit=-1" > /usr/local/etc/php/conf.d/test.ini
RUN mkdir -p ${MAIN_DIR} && chown www-data:www-data ${MAIN_DIR}
COPY --link . /var/www/html/
RUN composer install --no-interaction
USER www-data

# Etap produkcyjny
FROM base AS prod
ENV APP_ENV=prod

RUN mkdir -p ${MAIN_DIR} && chown www-data:www-data ${MAIN_DIR}

COPY --link composer.* symfony.* ./
RUN set -eux; \
    composer install --no-cache --prefer-dist --no-autoloader --no-scripts --no-progress --no-dev

COPY --link . /var/www/html/

RUN set -eux; \
    composer dump-autoload --classmap-authoritative --no-dev; \
    composer dump-env prod; \
    chown -R www-data:www-data /var/www/html/var; \
    chmod -R 777 /var/www/html/var

USER www-data