# Gunakan versi PHP 8.4 sesuai dengan composer.json kamu
FROM php:8.4-apache

# Install ekstensi yang dibutuhkan oleh Laravel, Filament, dan PHPWord
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    zip \
    unzip \
    && docker-php-ext-install intl zip pdo_mysql gd

# Perbaikan MPM: Matikan modul yang bentrok dan gunakan prefork yang stabil
RUN a2dismod mpm_event mpm_worker && a2enmod mpm_prefork

# Copy kode aplikasi
COPY . /var/www/html
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Setup Apache: Aktifkan rewrite dan ubah DocumentRoot ke /public
RUN a2enmod rewrite
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www/html

# Install dependensi (bypass pengecekan ekstensi sistem untuk grpc yang hilang)
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Set permissions untuk storage dan cache Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Jalankan Apache
CMD ["apache2-foreground"]