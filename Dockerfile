# Gunakan base image PHP 8.4 Apache
FROM php:8.4-apache

# 1. Install semua dependensi sistem dan ekstensi PHP yang diperlukan
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libpq-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install intl zip pdo_mysql pdo_pgsql gd

# 2. Perbaikan Anti-Eror MPM:
# Menonaktifkan semua modul MPM bawaan dan memaksa hanya menggunakan prefork
RUN a2dismod -f mpm_event mpm_worker mpm_event && a2enmod mpm_prefork

# 3. Setup Apache untuk Laravel
# Mengaktifkan rewrite dan merubah DocumentRoot ke folder /public
RUN a2enmod rewrite
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# 4. Copy aplikasi dan install Composer
COPY . /var/www/html
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 5. Install dependensi dengan bypass check
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# 6. Set permissions agar storage bisa diakses Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 7. Eksekusi Apache secara manual (Mencegah script bawaan image yang sering eror)
# Kita gunakan apache2ctl -D FOREGROUND
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]