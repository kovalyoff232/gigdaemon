# --- ЭТАП 1: Установка зависимостей PHP (Composer) ---
FROM composer:2 as vendor
WORKDIR /app
COPY database/ database/
COPY composer.json composer.json
COPY composer.lock composer.lock
RUN composer install --no-interaction --no-dev --optimize-autoloader

# --- ЭТАП 2: Сборка фронтенда (NPM) ---
FROM node:18 as frontend
WORKDIR /app
COPY package.json package.json
COPY package-lock.json* package-lock.json
RUN npm install
COPY . .
RUN npm run build

# --- ЭТАП 3: Финальный образ для продакшена ---
FROM php:8.2-fpm-alpine
WORKDIR /var/www/html
RUN apk add --no-cache bash libzip-dev zip postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql zip

# Копируем собранные артефакты
COPY --from=vendor /app/vendor/ /var/www/html/vendor/
COPY --from=vendor /app/database/ /var/www/html/database/
COPY --from=frontend /app/public/build/ /var/www/html/public/build/
COPY . .

# === ИЗМЕНЕНИЯ ЗДЕСЬ ===
# Копируем наш новый скрипт запуска
COPY start.sh .
# Делаем его исполняемым
RUN chmod +x ./start.sh

# Отдаем права на файлы веб-серверу
RUN chown -R www-data:www-data /var/www/html

# Указываем, что при запуске контейнера нужно выполнить наш скрипт
CMD ["./start.sh"]