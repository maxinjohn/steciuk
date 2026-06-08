#!/bin/sh
set -e

cd /var/www/html

# Ensure runtime directories exist
mkdir -p storage/database \
    storage/app/public \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

# Create SQLite database if missing
if [ ! -f storage/database/database.sqlite ]; then
    touch storage/database/database.sqlite
    chown www-data:www-data storage/database/database.sqlite
fi

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Wait for writable storage
if [ ! -w storage/database/database.sqlite ]; then
    echo "ERROR: storage/database is not writable"
    exit 1
fi

# Generate key if missing
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    php artisan key:generate --force --no-interaction
fi

php artisan storage:link --force 2>/dev/null || true

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force --no-interaction
fi

if [ "${RUN_SEED:-false}" = "true" ]; then
    php artisan db:seed --force --no-interaction
fi

if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache --no-interaction
    php artisan route:cache --no-interaction
    php artisan view:cache --no-interaction
    php artisan event:cache --no-interaction 2>/dev/null || true
else
    php artisan config:clear --no-interaction 2>/dev/null || true
fi

echo "STECI UK Parish ready — env=${APP_ENV}"

exec "$@"
