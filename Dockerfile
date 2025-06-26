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
WORKDIR /var/www/html

# Устанавливаем системные пакеты
RUN apk add --no-cache bash nginx libzip-dev zip postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql zip

# === ИЗМЕНЕНИЕ ЗДЕСЬ: КОПИРУЕМ КОНФИГУРАЦИЮ PHP-FPM ===
COPY docker/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Копируем Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Копируем весь код приложения
COPY . .

# Копируем собранные артефакты
COPY --from=vendor /app/vendor/ /var/www/html/vendor/
COPY --from=frontend /app/public/build/ /var/www/html/public/build/

# Запускаем скрипты Composer
RUN composer dump-autoload --optimize

# Создаем директорию для сокета и настраиваем права доступа
RUN mkdir -p /var/run/php \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/run/php \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Копируем и делаем исполняемым наш скрипт запуска
COPY start.sh .
RUN chmod +x ./start.sh

# Команда для запуска контейнера
CMD ["./start.sh"]