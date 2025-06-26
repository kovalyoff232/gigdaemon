# --- ЭТАП 1: Сборка фронтенда (NPM) ---
# Используем стабильную LTS-версию Node.js
FROM node:20-alpine as frontend

WORKDIR /app

# Копируем только файлы зависимостей
COPY package.json package.json
COPY package-lock.json* package-lock.json

# Устанавливаем зависимости
RUN npm install

# Копируем весь остальной код, чтобы собрать фронтенд
COPY . .
RUN npm run build


# --- ЭТАП 2: Финальный образ для продакшена ---
# Используем официальный образ Composer для установки PHP-зависимостей
FROM composer:2 as vendor
WORKDIR /app
COPY database/ database/
COPY composer.json composer.json
COPY composer.lock composer.lock
# Устанавливаем зависимости, но не запускаем скрипты, так как еще нет всего кода
RUN composer install --no-interaction --no-dev --no-scripts --optimize-autoloader


# --- ЭТАП 3: Собираем все вместе ---
FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

# Устанавливаем системные пакеты
RUN apk add --no-cache bash libzip-dev zip postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql zip

# Копируем весь код приложения
COPY . .

# Копируем собранные артефакты из предыдущих этапов
COPY --from=vendor /app/vendor/ /var/www/html/vendor/
COPY --from=frontend /app/public/build/ /var/www/html/public/build/

# Теперь, когда все на месте, запускаем скрипты Composer
RUN composer dump-autoload --optimize

# Настраиваем права доступа
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Копируем и делаем исполняемым наш скрипт запуска
COPY start.sh .
RUN chmod +x ./start.sh

# Команда для запуска контейнера
CMD ["./start.sh"]