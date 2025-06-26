#!/bin/sh

# Этот скрипт будет запущен внутри контейнера на Render

# 1. Запускаем миграции базы данных.
echo "Running migrations..."
php artisan migrate --force

# 2. Запускаем веб-сервер Laravel.
echo "Starting Laravel server..."
php artisan serve --host=0.0.0.0 --port=10000