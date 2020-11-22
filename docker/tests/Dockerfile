FROM php:7.4-cli-alpine AS bookstore-php

WORKDIR /var/www/html

RUN apk add composer
RUN apk add postgresql-dev
RUN docker-php-ext-install pdo pdo_pgsql

COPY php.ini /usr/local/etc/php/conf.d

EXPOSE 9000
