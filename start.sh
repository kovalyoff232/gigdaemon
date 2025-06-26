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

# 4. Запускаем Nginx на переднем плане.
# Эта команда будет удерживать контейнер в рабочем состоянии.
echo "Starting Nginx..."
nginx -g "daemon off;"