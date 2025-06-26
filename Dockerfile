# --- ЭТАП 1: Установка зависимостей PHP (Composer) ---
# Используем официальный образ Composer
FROM composer:2 as vendor

# Устанавливаем рабочую директорию
WORKDIR /app

# Копируем файлы зависимостей
COPY database/ database/
COPY composer.json composer.json
COPY composer.lock composer.lock

# Устанавливаем зависимости, игнорируя dev-пакеты
RUN composer install --no-interaction --no-dev --optimize-autoloader


# --- ЭТАП 2: Сборка фронтенда (NPM) ---
# Используем официальный образ Node.js
FROM node:18 as frontend

# Устанавливаем рабочую директорию
WORKDIR /app

# Копируем файлы зависимостей
COPY package.json package.json
COPY package-lock.json package-lock.json

# Устанавливаем зависимости
RUN npm install

# Копируем остальные файлы для сборки
COPY . .

# Собираем production-версию ассетов
RUN npm run build


# --- ЭТАП 3: Финальный образ для продакшена ---
# Используем легковесный образ PHP-FPM на Alpine Linux
FROM php:8.2-fpm-alpine

# Устанавливаем рабочую директорию
WORKDIR /var/www/html

# Устанавливаем необходимые системные пакеты и расширения PHP
RUN apk add --no-cache \
      bash \
      libzip-dev \
      zip \
      postgresql-dev \
    && docker-php-ext-install \
      pdo \
      pdo_pgsql \
      zip

# Копируем собранные зависимости Composer из первого этапа
COPY --from=vendor /app/vendor/ /var/www/html/vendor/
COPY --from=vendor /app/database/ /var/www/html/database/

# Копируем собранные ассеты из второго этапа
COPY --from=frontend /app/public/build/ /var/www/html/public/build/

# Копируем весь остальной код приложения
COPY . .

# Отдаем права на файлы веб-серверу
RUN chown -R www-data:www-data /var/www/html

# Эта команда будет выполнена при запуске контейнера, но Render переопределит ее своей Start Command
# CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]