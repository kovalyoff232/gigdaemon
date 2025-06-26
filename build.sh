#!/usr/bin/env bash
# exit on error
set -o errexit

# Эти команды будут выполнены на сервере Render
composer install --no-dev --no-interaction --optimize-autoloader

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

npm install
npm run build