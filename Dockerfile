# --- ЭТАП 1: Установка зависимостей PHP (Composer) ---
# Мы не запускаем скрипты здесь, чтобы избежать ошибки с artisan
FROM composer:2 as vendor
WORKDIR /app
COPY database/ database/
COPY composer.json composer.json
COPY composer.lock composer.lock
RUN composer install --no-interaction --no-dev --no-scripts --optimize-autoloader

# --- ЭТАП 2: Сборка фронтенда (NPM) ---
# Используем более новую LTS-версию Node, чтобы избежать проблем с crypto
FROM node:20 as frontend
WORKDIR /app
COPY package.json package.json
COPY package-lock.json* package-lock.json
RUN npm install
COPY . .
RUN npm run build

# --- ЭТАП 3: Финальный образ для продакшена ---
FROM php:8.2-fpm-alpine
WORKDIR /var/www/html
# Устанавливаем системные пакеты
RUN apk add --no-cache bash libzip-dev zip postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql zip

# Копируем весь код приложения СНАЧАЛА
COPY . .
# А ПОТОМ копируем поверх него уже собранные зависимости
COPY --from=vendor /app/vendor/ /var/www/html/vendor/
COPY --from=frontend /app/public/build/ /var/www/html/public/build/

# Теперь, когда весь код на месте, мы можем запустить Composer-скрипты
RUN composer dump-autoload --optimize

# Отдаем права на файлы веб-серверу
RUN chown -R www-data:www-data /var/www/html

# Копируем наш скрипт запуска и делаем его исполняемым
COPY start.sh .
RUN chmod +x ./start.sh

# Команда для запуска контейнера
CMD ["./start.sh"]