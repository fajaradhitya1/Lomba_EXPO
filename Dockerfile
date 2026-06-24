# Ubah ke 8.4 agar sesuai dengan composer.json kamu
FROM php:8.4-apache

# Install ekstensi yang dibutuhkan (tambahkan libpng-dev untuk gd)
RUN apt-get update && apt-get install -y libicu-dev libzip-dev libpng-dev zip
RUN docker-php-ext-install intl zip pdo_mysql gd

# Copy kode aplikasi
COPY . /var/www/html
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Setup Apache
RUN a2enmod rewrite
WORKDIR /var/www/html

# Install dependensi
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

