#!/bin/sh
set -e
php artisan config:clear
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link || true
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"