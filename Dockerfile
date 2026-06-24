FROM php:8.4-apache

# 1. Install ekstensi yang dibutuhkan termasuk pdo_pgsql
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-install intl zip pdo_mysql pdo_pgsql gd

# 2. Perbaikan MPM: Matikan modul yang bentrok dan gunakan prefork
RUN a2dismod mpm_event mpm_worker && a2enmod mpm_prefork

# 3. Copy kode aplikasi
COPY . /var/www/html
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Setup Apache
RUN a2enmod rewrite
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# 5. Install dependensi
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# 6. Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 7. Gunakan CMD yang bersih
CMD ["apache2-foreground"]