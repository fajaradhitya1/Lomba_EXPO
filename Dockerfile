FROM php:8.4-apache

# 1. Install ekstensi
RUN apt-get update && apt-get install -y \
    libicu-dev libzip-dev libpng-dev libpq-dev zip unzip git \
    && docker-php-ext-install intl zip pdo_mysql pdo_pgsql gd

# 2. Hapus total semua konfigurasi MPM bawaan
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load \
    && rm -f /etc/apache2/mods-enabled/mpm_*.conf

# 3. Aktifkan HANYA prefork
RUN a2enmod mpm_prefork rewrite

# 4. Setup Laravel Public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# 5. Copy aplikasi
COPY . /var/www/html
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html

# 6. Install dependensi
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# 7. Permission & Entrypoint
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# PENTING: Gunakan exec agar Apache menangkap sinyal dengan benar
CMD ["apache2ctl", "-D", "FOREGROUND"]