FROM php:8.4.5-fpm

COPY . /app

WORKDIR /app

RUN apt-get update \
&& apt-get install -y zip unzip libzip-dev default-mysql-client \
&& docker-php-ext-install zip pdo_mysql
