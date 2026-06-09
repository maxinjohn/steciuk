#!/usr/bin/env sh
# Smoke-test Docker production boot: build, start fresh, verify health + pages.
set -e

cd "$(dirname "$0")/.."

PORT="${NGINX_HTTP_PORT:-8089}"
COMPOSE="docker compose -p steci-smoke"

echo "==> Building image..."
$COMPOSE build

echo "==> Starting container (fresh volumes)..."
$COMPOSE down -v 2>/dev/null || true

export NGINX_HTTP_PORT="$PORT"
export APP_ENV=production
export APP_DEBUG=false
export APP_KEY="${APP_KEY:-base64:$(openssl rand -base64 32)}"
export SEED_MODE=off
export AUTO_BOOTSTRAP=true
export RUN_SEED=false
export DB_DATABASE=/var/www/html/storage/database/database.sqlite

$COMPOSE up -d

echo "==> Waiting for healthcheck..."
TRIES=30
until curl -sf "http://127.0.0.1:${PORT}/up" >/dev/null 2>&1; do
    TRIES=$((TRIES - 1))
    if [ "$TRIES" -le 0 ]; then
        echo "FAIL: /up did not become healthy"
        $COMPOSE logs --tail=80
        exit 1
    fi
    sleep 2
done

echo "==> Checking public routes..."
curl -sf "http://127.0.0.1:${PORT}/" | grep -q "STECI" || { echo "FAIL: home"; exit 1; }
curl -sf "http://127.0.0.1:${PORT}/events" | grep -q "data-menu-item" || { echo "FAIL: events nav"; exit 1; }
curl -sf "http://127.0.0.1:${PORT}/images/steci-mark.svg" | grep -q "svg" || { echo "FAIL: logo"; exit 1; }

echo "==> Checking seeded data in container..."
$COMPOSE exec -T app php artisan tinker --execute="echo App\Models\Page::count().' pages';" | grep -qv "^0 pages" || { echo "FAIL: no pages seeded"; exit 1; }

echo "OK: Docker production smoke test passed on port ${PORT}"
$COMPOSE down -v
