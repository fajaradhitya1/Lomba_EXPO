FROM php:8.4-apache

# 1. Install ekstensi yang dibutuhkan termasuk driver PostgreSQL (libpq-dev)
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-install intl zip pdo_mysql pdo_pgsql gd

# 2. PERBAIKAN MPM: Matikan modul yang bentrok, aktifkan prefork yang stabil
RUN a2dismod mpm_event mpm_worker && a2enmod mpm_prefork

# 3. Setup Apache agar mengarah ke folder public
RUN a2enmod rewrite
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# 4. Copy kode aplikasi
COPY . /var/www/html
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 5. Install dependensi
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# 6. Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 7. Jalankan Apache
CMD ["apache2-foreground"]