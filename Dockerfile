# --- ЭТАП 1: Устанавливаем зависимости PHP ---
# Используем официальный образ Composer, чтобы получить сам Composer.
FROM composer:2.7 as vendor
WORKDIR /app
# Копируем только файлы для зависимостей.
COPY database/ database/
COPY composer.json composer.json
COPY composer.lock composer.lock
# Устанавливаем зависимости без запуска скриптов, чтобы избежать ошибок с artisan.
RUN composer install --no-interaction --no-scripts --no-dev --optimize-autoloader

# --- ЭТАП 2: Собираем фронтенд ---
# Используем современную и стабильную LTS-версию Node.
FROM node:20-alpine as frontend
WORKDIR /app
COPY package.json package.json
COPY package-lock.json* package-lock.json
RUN npm install
COPY . .
RUN npm run build

# --- ЭТАП 3: Собираем финальный образ ---
# Начинаем с чистого PHP.
FROM php:8.2-fpm-alpine
WORKDIR /var/www/html

# Устанавливаем системные зависимости, необходимые для Laravel и Postgres.
# 'bash' нужен для нашего start.sh скрипта.
RUN apk add --no-cache \
      bash \
      libzip-dev \
      zip \
      postgresql-dev \
    && docker-php-ext-install \
      pdo \
      pdo_pgsql \
      zip

# Копируем Composer из его официального образа, чтобы он был доступен в финальном контейнере.
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Копируем весь код приложения.
COPY . .

# Копируем УЖЕ собранные зависимости из предыдущих этапов.
COPY --from=vendor /app/vendor/ /var/www/html/vendor/
COPY --from=frontend /app/public/build/ /var/www/html/public/build/

# Теперь, когда весь код и Composer на месте, мы можем безопасно запустить скрипты Laravel.
RUN composer dump-autoload --optimize

# Настраиваем права доступа для папок, в которые Laravel будет писать.
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Копируем наш скрипт запуска и делаем его исполняемым.
COPY start.sh .
RUN chmod +x ./start.sh

# Указываем, что этот скрипт нужно запустить, когда контейнер стартует.
CMD ["./start.sh"]