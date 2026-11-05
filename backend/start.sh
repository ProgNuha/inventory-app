#!/bin/sh
set -e
php artisan config:clear
if [ "$FRESH_SEED" = "true" ]; then
  php artisan migrate:fresh --seed --force
else
  php artisan migrate --force
  php artisan db:seed --force
fi
php artisan storage:link || true
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"