FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip exif pcntl bcmath gd

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install dependencies
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Configure Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copy custom Apache configuration
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf
RUN a2ensite 000-default.conf

# Create .htaccess file
RUN echo 'DirectoryIndex api/translations.php\n\
RewriteEngine On\n\
RewriteCond %{REQUEST_FILENAME} !-f\n\
RewriteCond %{REQUEST_FILENAME} !-d\n\
RewriteRule ^(.*)$ api/translations.php [QSA,L]' > /var/www/html/.htaccess

# Configure PHP
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && sed -i 's/memory_limit = .*/memory_limit = 256M/' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/max_execution_time = .*/max_execution_time = 60/' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/upload_max_filesize = .*/upload_max_filesize = 64M/' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/post_max_size = .*/post_max_size = 64M/' "$PHP_INI_DIR/php.ini"

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"] 
