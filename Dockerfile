FROM php:7.3

RUN apt-get update && apt-get install -y --no-install-recommends \
libmcrypt-dev \
default-mysql-client \
libmagickwand-dev

RUN pecl install imagick
RUN docker-php-ext-enable imagick
RUN docker-php-ext-install pdo_mysql
RUN apt-get install -y --no-install-recommends libzip-dev
RUN docker-php-ext-install zip

WORKDIR /app