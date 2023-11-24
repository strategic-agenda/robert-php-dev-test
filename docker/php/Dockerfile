# Use PHP 8.2 image
FROM php:8.2-apache

# Install PDO extension (modify as needed for your database)
RUN docker-php-ext-install pdo_mysql

# Copy your source code to the container
COPY . /var/www/html/

# Expose port 80
EXPOSE 80
