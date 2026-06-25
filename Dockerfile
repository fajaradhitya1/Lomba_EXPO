# ===== Stage 1: Build frontend assets =====
FROM node:20-alpine AS node-builder

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build

# ===== Stage 2: PHP application =====
FROM php:8.4-apache

RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libpq-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install intl zip pdo_mysql pdo_pgsql gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

RUN sed -i 's|/var/www/html|/var/www/html/public|g' \
    /etc/apache2/sites-available/000-default.conf

COPY . /var/www/html

# Copy hasil build Vite dari stage Node
COPY --from=node-builder /app/public/build /var/www/html/public/build

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --ignore-platform-reqs

RUN chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

RUN printf '%s\n' \
    '#!/bin/bash' \
    'set -e' \
    '' \
    'echo "=== Cleaning up MPM modules ==="' \
    'a2dismod mpm_event mpm_worker mpm_prefork 2>/dev/null || true' \
    'rm -f /etc/apache2/mods-enabled/mpm_event.* \' \
    '      /etc/apache2/mods-enabled/mpm_worker.* \' \
    '      /etc/apache2/mods-enabled/mpm_prefork.*' \
    '' \
    'echo "=== Enabling mpm_prefork only ==="' \
    'a2enmod mpm_prefork' \
    '' \
    'echo "=== Active modules ==="' \
    'apache2ctl -M || true' \
    '' \
    'echo "=== Testing Apache config ==="' \
    'apache2ctl -t' \
    '' \
    'echo "=== Starting Apache ==="' \
    'exec apache2-foreground' \
    > /usr/local/bin/start-apache.sh \
    && chmod +x /usr/local/bin/start-apache.sh

CMD ["/usr/local/bin/start-apache.sh"]