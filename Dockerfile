# --- АРГУМЕНТЫ ---
ARG PHP_VERSION=8.2
ARG NODE_VERSION=20
ARG COMPOSER_VERSION=2

# --- ЭТАП 1: Базовый образ PHP ---
FROM php:${PHP_VERSION}-fpm-alpine AS base
WORKDIR /var/www/html

# Устанавливаем системные зависимости
RUN apk add --no-cache \
    bash \
    curl \
    libzip-dev \
    zip \
    postgresql-dev

# Устанавливаем расширения PHP
RUN docker-php-ext-install pdo pdo_pgsql zip

# Устанавливаем Composer
COPY --from=composer:${COMPOSER_VERSION} /usr/bin/composer /usr/bin/composer


# --- ЭТАП 2: Установка PHP зависимостей ---
FROM base AS vendor
WORKDIR /var/www/html

# Копируем файлы и устанавливаем зависимости
COPY database/ database/
COPY composer.json composer.json
COPY composer.lock composer.lock
RUN composer install --no-interaction --no-dev --optimize-autoloader


# --- ЭТАП 3: Сборка фронтенда ---
FROM node:${NODE_VERSION}-alpine AS frontend
WORKDIR /app

COPY package.json package.json
COPY package-lock.json* package-lock.json
RUN npm install

COPY . .
RUN npm run build


# --- ЭТАП 4: Финальный образ для продакшена ---
FROM base AS final
WORKDIR /var/www/html

# Копируем весь код приложения
COPY . .

# Копируем собранные артефакты из предыдущих этапов
COPY --from=vendor /var/www/html/vendor/ /var/www/html/vendor/
COPY --from=frontend /app/public/build/ /var/www/html/public/build/

# Настраиваем права доступа
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Копируем и делаем исполняемым наш скрипт запуска
COPY start.sh .
RUN chmod +x ./start.sh

# Команда для запуска контейнера
CMD ["./start.sh"]