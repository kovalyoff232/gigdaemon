#!/bin/sh

# Этот скрипт будет запущен внутри контейнера на Render

# 1. Запускаем миграции базы данных.
echo "Running migrations..."
php artisan migrate --force

# 2. Кэшируем конфигурацию для продакшена.
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Запускаем PHP-FPM в фоновом режиме.
echo "Starting PHP-FPM..."
php-fpm -D

# 4. === ГЛАВНЫЙ ФИКС ===
# Мы принудительно указываем Nginx, какой конфигурационный файл использовать.
# Путь внутри контейнера - /var/www/html/docker/nginx.conf
# Это заставит его слушать на порту 10000, как и ожидает Render.
echo "Starting Nginx with correct config..."
nginx -c /var/www/html/docker/nginx.conf -g "daemon off;"