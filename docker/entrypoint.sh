#!/bin/sh
set -e

cd /var/www/html

STORAGE_ROOT="${APP_STORAGE_PATH:-/var/www/html/storage}"
DB_FILE="${DB_DATABASE:-${STORAGE_ROOT}/database/database.sqlite}"

# Ensure runtime directories exist
mkdir -p "${STORAGE_ROOT}/database" \
    "${STORAGE_ROOT}/app/public" \
    "${STORAGE_ROOT}/app/private" \
    "${STORAGE_ROOT}/framework/cache" \
    "${STORAGE_ROOT}/framework/sessions" \
    "${STORAGE_ROOT}/framework/views" \
    "${STORAGE_ROOT}/logs" \
    bootstrap/cache

# Create SQLite database if missing
if [ ! -f "$DB_FILE" ]; then
    mkdir -p "$(dirname "$DB_FILE")"
    touch "$DB_FILE"
    chown www-data:www-data "$DB_FILE"
fi

chown -R www-data:www-data "${STORAGE_ROOT}" bootstrap/cache
chmod -R 775 "${STORAGE_ROOT}" bootstrap/cache

# Wait for writable database
if [ ! -w "$DB_FILE" ]; then
    echo "ERROR: database file is not writable: $DB_FILE"
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
    SEED_MODE="${SEED_MODE:-bootstrap}"
    export SEED_MODE
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

export RUN_QUEUE_WORKER="${RUN_QUEUE_WORKER:-true}"
export RUN_SCHEDULER="${RUN_SCHEDULER:-true}"

echo "STECI UK Parish ready — env=${APP_ENV} db=${DB_FILE}"

exec "$@"
