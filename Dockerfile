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

RUN echo "date.timezone = UTC" > /usr/local/etc/php/conf.d/timezone.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN usermod -u 1000 www-data

# Dev/Test Stage (opcjonalny)
FROM base AS dev

#RUN pecl install xdebug \
#    && docker-php-ext-enable xdebug
#  TODO tu chyba jeszcze zend potrzebny będzie
#COPY --link docker/php/xdebug.ini $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini

# Prod Stage
FROM base AS prod
ENV APP_ENV=prod

COPY --link docker/php/php.ini $PHP_INI_DIR/conf.d/app.ini

COPY --link composer.* symfony.* ./
# TODO tu było dodatkowo w composer install --no-dev
RUN set -eux; \
    composer install --no-cache --prefer-dist --no-autoloader --no-scripts --no-progress

COPY --link . /var/www/html/
# TODO tu było dodatkowo w composer dump-autoload --no-dev
RUN set -eux; \
    composer dump-autoload --classmap-authoritative; \
    composer dump-env prod; \
    composer run-script --no-dev post-install-cmd; \
    chown -R www-data:www-data /var/www/html; \
    chmod -R 777 /var/www/html/var; sync;

USER www-data

CMD ["php-fpm"]