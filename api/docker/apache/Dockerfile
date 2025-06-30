FROM php:8.4.8-apache

# Install necessary extensions
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libzip-dev \
        zip \
        nano \
        mlocate \
    && docker-php-ext-install -j$(nproc) iconv gd zip bcmath mysqli pdo pdo_mysql

# Install Redis
RUN pecl install -o -f redis \
&&  rm -rf /tmp/pear \
&&  docker-php-ext-enable redis

# Install xdebug
RUN yes | pecl install xdebug

# Enable rewrite
RUN a2enmod rewrite
RUN service apache2 restart

# Install coomposer
RUN curl --silent --show-error https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

WORKDIR /var/www/html
