FROM php:8.4-apache

# Install ekstensi yang dibutuhkan
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    zip \
    unzip \
    && docker-php-ext-install intl zip pdo_mysql gd

# Perbaikan MPM (Menghilangkan error More than one MPM)
RUN a2dismod mpm_event mpm_worker && a2enmod mpm_prefork

# Konfigurasi Apache
RUN a2enmod rewrite
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Copy aplikasi dan Composer
COPY . /var/www/html
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install dependensi
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# --- PENAMBAHAN: Entrypoint untuk migrasi dan optimasi ---
# Kita buat script kecil untuk menjalankan perintah Laravel sebelum Apache start
RUN echo '#!/bin/sh' > /usr/local/bin/entrypoint.sh && \
    echo 'php artisan migrate --force' >> /usr/local/bin/entrypoint.sh && \
    echo 'php artisan config:cache' >> /usr/local/bin/entrypoint.sh && \
    echo 'php artisan route:cache' >> /usr/local/bin/entrypoint.sh && \
    echo 'apache2-foreground' >> /usr/local/bin/entrypoint.sh && \
    chmod +x /usr/local/bin/entrypoint.sh

# Gunakan script entrypoint sebagai perintah utama
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]