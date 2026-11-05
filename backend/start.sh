#!/bin/sh
set -e
# APP_KEY opsional: bila belum diisi (mis. docker compose lokal tanpa .env), buat otomatis
if [ -z "$APP_KEY" ]; then
  export APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
fi
php artisan config:clear
if [ "$FRESH_SEED" = "true" ]; then
  php artisan migrate:fresh --seed --force
else
  php artisan migrate --force
  php artisan db:seed --force
fi
php artisan storage:link || true
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"