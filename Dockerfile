# --- STAGE 1: PHP Dependencies (Composer) ---
FROM composer:2.7 as vendor
WORKDIR /app
COPY database/ database/
COPY composer.json composer.json
COPY composer.lock composer.lock
RUN composer install --no-interaction --no-scripts --no-dev --optimize-autoloader

# --- STAGE 2: Frontend Assets (NPM) ---
FROM node:20-alpine as frontend
WORKDIR /app
COPY package.json package.json
COPY package-lock.json* package-lock.json
RUN npm install
COPY . .
RUN npm run build

# --- STAGE 3: Final Production Image ---
FROM php:8.2-fpm-alpine
WORKDIR /var/www/html

# Install system dependencies: PHP extensions and Nginx
RUN apk add --no-cache \
      bash \
      nginx \
      libzip-dev \
      zip \
      postgresql-dev \
    && docker-php-ext-install \
      pdo \
      pdo_pgsql \
      zip

# Copy Composer binary
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Copy application code
COPY . .

# Copy pre-built dependencies
COPY --from=vendor /app/vendor/ /var/www/html/vendor/
COPY --from=frontend /app/public/build/ /var/www/html/public/build/

# Generate optimized autoloader
RUN composer dump-autoload --optimize

# --- FIX: CREATE THE DIRECTORY BEFORE SETTING PERMISSIONS ---
# Create the run directory for PHP-FPM socket
RUN mkdir -p /var/run/php

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/run/php
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy start script and make it executable
COPY start.sh .
RUN chmod +x ./start.sh

# Entrypoint
CMD ["./start.sh"]