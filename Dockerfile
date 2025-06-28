# Dockerfile
FROM php:8.3-fpm

# 1. Install system deps (for pdo_mysql, zip, etc)
RUN apt-get update \
 && apt-get install -y \
      libzip-dev \
      zip \
      unzip \
      default-mysql-client \
      git \
      libonig-dev \
 && rm -rf /var/lib/apt/lists/*

# 2. Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring

# 3. Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 4. Set working dir
WORKDIR /var/www/html

# 5. Copy in code & install Composer deps
COPY . /var/www/html
RUN composer install --no-interaction --optimize-autoloader

# 6. Fix permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# 7. Start PHP-FPM
CMD ["php-fpm"]
