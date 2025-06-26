# --- ЭТАП 1: Установка зависимостей PHP ---
FROM composer:2.7 as vendor
WORKDIR /app
COPY database/ database/
COPY composer.json composer.json
COPY composer.lock composer.lock
RUN composer install --no-interaction --no-scripts --no-dev --optimize-autoloader

# --- ЭТАП 2: Сборка фронтенда ---
FROM node:20-alpine as frontend
WORKDIR /app
COPY package.json package.json
COPY package-lock.json* package-lock.json
RUN npm install
COPY . .
RUN npm run build

# --- ЭТАП 3: Собираем финальный образ ---
FROM php:8.2-fpm-alpine

# Устанавливаем системные пакеты: PHP-расширения и NGINX
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

# Копируем нашу конфигурацию Nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf

WORKDIR /var/www/html

# Копируем Composer из его официального образа
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Копируем весь код приложения
COPY . .

# Копируем УЖЕ собранные зависимости
COPY --from=vendor /app/vendor/ /var/www/html/vendor/
COPY --from=frontend /app/public/build/ /var/www/html/public/build/

# Запускаем скрипты Composer
RUN composer dump-autoload --optimize

# Настраиваем права доступа
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/run/php

# Копируем и делаем исполняемым наш скрипт запуска
COPY start.sh .
RUN chmod +x ./start.sh

# Запускаем наш скрипт
CMD ["./start.sh"]