#!/bin/sh
set -e

cd /var/www/html

STORAGE_ROOT="${APP_STORAGE_PATH:-/var/www/html/storage}"
DB_FILE="${DB_DATABASE:-${STORAGE_ROOT}/database/database.sqlite}"

# Resolve relative SQLite paths against the app root (Docker .env often uses storage/...)
case "$DB_FILE" in
    /*) ;;
    *) DB_FILE="/var/www/html/${DB_FILE#./}" ;;
esac

export DB_DATABASE="$DB_FILE"
export APP_STORAGE_PATH="$STORAGE_ROOT"

mkdir -p "${STORAGE_ROOT}/database" \
    "${STORAGE_ROOT}/app/public" \
    "${STORAGE_ROOT}/app/private" \
    "${STORAGE_ROOT}/framework/cache" \
    "${STORAGE_ROOT}/framework/sessions" \
    "${STORAGE_ROOT}/framework/views" \
    "${STORAGE_ROOT}/logs" \
    bootstrap/cache

if [ ! -f "$DB_FILE" ]; then
    mkdir -p "$(dirname "$DB_FILE")"
    touch "$DB_FILE"
    chown www-data:www-data "$DB_FILE"
fi

chown -R www-data:www-data "${STORAGE_ROOT}" bootstrap/cache
chmod -R 775 "${STORAGE_ROOT}" bootstrap/cache

if [ ! -w "$DB_FILE" ]; then
    echo "ERROR: database file is not writable: $DB_FILE"
    exit 1
fi

if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    if [ "${APP_ENV:-local}" = "production" ]; then
        echo "ERROR: Set APP_KEY in .env before production deploy (php artisan key:generate --show)."
        exit 1
    fi

    php artisan key:generate --force --no-interaction
fi

# Composer install in the image uses --no-scripts; discover providers before migrate/seed.
php artisan package:discover --ansi --no-interaction >/dev/null 2>&1 || true

php artisan storage:link --force 2>/dev/null || true

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force --no-interaction
fi

php artisan site:ensure-roles --force --no-interaction 2>/dev/null || true
php artisan site:ensure-admin --force --no-interaction 2>/dev/null || true

if [ "${RUN_SEED:-false}" = "true" ]; then
    export SEED_MODE="${SEED_MODE:-bootstrap}"
    php artisan db:seed --force --no-interaction
elif [ "${AUTO_BOOTSTRAP:-true}" = "true" ]; then
    php artisan site:bootstrap-if-empty --force --no-interaction
elif [ "${AUTO_SYNC_REFERENCE:-false}" = "true" ]; then
    php artisan site:sync-reference-data --force --no-interaction
fi

# Warm caches after reference data is present (bootstrap/sync commands also clear site caches).
php artisan cache:clear --no-interaction 2>/dev/null || true

if [ "$APP_ENV" = "production" ]; then
    echo "opcache.validate_timestamps=0" > /usr/local/etc/php/conf.d/zz-opcache-prod.ini
    php artisan optimize --no-interaction
else
    rm -f /usr/local/etc/php/conf.d/zz-opcache-prod.ini
    php artisan config:clear --no-interaction 2>/dev/null || true
fi

if [ "${QUEUE_CONNECTION:-sync}" != "sync" ] && [ "${RUN_QUEUE_WORKER:-false}" = "true" ]; then
    export RUN_QUEUE_WORKER=true
else
    export RUN_QUEUE_WORKER=false
fi

export RUN_SCHEDULER="${RUN_SCHEDULER:-true}"

echo "STECI ready — env=${APP_ENV} db=${DB_FILE} queue=${QUEUE_CONNECTION:-sync} bootstrap=${AUTO_BOOTSTRAP:-true}"

exec "$@"
