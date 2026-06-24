FROM php:8.3-apache

# Install ekstensi yang dibutuhkan
RUN apt-get update && apt-get install -y libicu-dev libzip-dev zip
RUN docker-php-ext-install intl zip pdo_mysql

# Copy kode aplikasi
COPY . /var/www/html
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Setup Apache
RUN a2enmod rewrite
COPY --chown=www-data:www-data . /var/www/html
WORKDIR /var/www/html

# Install dependensi
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache