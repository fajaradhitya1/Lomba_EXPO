FROM php:8.4-apache

# 1. Install apache2 dan dependensi PHP
RUN apt-get update && apt-get install -y \
    apache2 \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libpq-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install intl zip pdo_mysql pdo_pgsql gd

# 2. Hapus total semua konfigurasi Apache bawaan
RUN rm -rf /etc/apache2/sites-enabled/* /etc/apache2/sites-available/* /etc/apache2/mods-enabled/*

# 3. Buat konfigurasi minimal untuk prefork
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && a2enmod mpm_prefork rewrite

# 4. Tambahkan vhost minimal
RUN echo "<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>" > /etc/apache2/sites-available/000-default.conf \
    && a2ensite 000-default

# 5. Copy aplikasi
COPY . /var/www/html
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html

# 6. Install dependensi
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# 7. Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 8. Start Apache
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]