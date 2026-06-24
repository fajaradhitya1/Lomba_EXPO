FROM php:8.4-apache

# 1. Install dependensi
RUN apt-get update && apt-get install -y \
    libicu-dev libzip-dev libpng-dev libpq-dev zip unzip git \
    && docker-php-ext-install intl zip pdo_mysql pdo_pgsql gd

# 2. Hapus total semua konfigurasi Apache yang berpotensi bentrok
RUN rm -rf /etc/apache2/mods-enabled/* \
    && a2enmod mpm_prefork rewrite

# 3. Paksa Apache untuk tidak memuat modul selain prefork
RUN echo "LoadModule mpm_prefork_module /usr/lib/apache2/modules/mod_mpm_prefork.so" > /etc/apache2/mods-enabled/mpm_prefork.load

# 4. Setup Laravel Public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# 5. Copy aplikasi
COPY . /var/www/html
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html

# 6. Install dependensi
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# 7. Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 8. Gunakan perintah langsung, BUKAN apache2-foreground
CMD ["/usr/sbin/apache2", "-D", "FOREGROUND"]