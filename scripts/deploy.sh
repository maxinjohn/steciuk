#!/usr/bin/env bash
# =============================================================================
# STECI UK Parish — production deploy
#
# Run on the server from the project root after merging dev → main:
#
#   ./scripts/deploy.sh
#   ./scripts/deploy.sh --sync          # also upsert reference data from seeders
#   ./scripts/deploy.sh --branch main
#   ./scripts/deploy.sh --no-pull       # skip git pull (image already updated)
#
# Typical workflow:
#   1. Develop on `dev`, open PR / merge to `main`
#   2. SSH to production server
#   3. ./scripts/deploy.sh
# =============================================================================

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

# shellcheck disable=SC1091
[ -f "$ROOT_DIR/scripts/deploy.env" ] && source "$ROOT_DIR/scripts/deploy.env"

DEPLOY_BRANCH="${DEPLOY_BRANCH:-main}"
DEPLOY_GIT_REMOTE="${DEPLOY_GIT_REMOTE:-origin}"
DEPLOY_COMPOSE="${DEPLOY_COMPOSE:-docker compose}"
DEPLOY_HEALTH_TIMEOUT="${DEPLOY_HEALTH_TIMEOUT:-120}"

DO_PULL=1
DO_BUILD=1
DO_SYNC=0
FORCE_GIT=0

usage() {
    cat <<'EOF'
Usage: ./scripts/deploy.sh [options]

Options:
  --branch NAME    Git branch to pull (default: main, or DEPLOY_BRANCH)
  --sync           Run site:sync-reference-data after deploy (safe prod upsert)
  --no-pull        Skip git fetch/pull
  --no-build       Skip docker compose build (restart only)
  --force-git      Allow deploy with a dirty git working tree
  -h, --help       Show this help

Environment (optional scripts/deploy.env):
  DEPLOY_BRANCH, DEPLOY_GIT_REMOTE, DEPLOY_COMPOSE, DEPLOY_HEALTH_TIMEOUT
EOF
}

log()  { printf '\033[1;36m==>\033[0m %s\n' "$*"; }
warn() { printf '\033[1;33m!!>\033[0m %s\n' "$*"; }
die()  { printf '\033[1;31mERROR:\033[0m %s\n' "$*" >&2; exit 1; }

while [ $# -gt 0 ]; do
    case "$1" in
        --branch)
            DEPLOY_BRANCH="${2:?--branch requires a name}"
            shift 2
            ;;
        --sync)
            DO_SYNC=1
            shift
            ;;
        --no-pull)
            DO_PULL=0
            shift
            ;;
        --no-build)
            DO_BUILD=0
            shift
            ;;
        --force-git)
            FORCE_GIT=1
            shift
            ;;
        -h|--help)
            usage
            exit 0
            ;;
        *)
            die "Unknown option: $1 (try --help)"
            ;;
    esac
done

read_env() {
    local key="$1"
    local default="${2:-}"
    if [ ! -f .env ]; then
        echo "$default"
        return
    fi
    local line
    line="$(grep -E "^${key}=" .env 2>/dev/null | tail -1 || true)"
    if [ -z "$line" ]; then
        echo "$default"
        return
    fi
    local value="${line#*=}"
    value="${value%\"}"
    value="${value#\"}"
    value="${value%\'}"
    value="${value#\'}"
    echo "${value:-$default}"
}

require_cmd() {
    command -v "$1" >/dev/null 2>&1 || die "Missing required command: $1"
}

require_cmd docker
require_cmd git

if ! docker compose version >/dev/null 2>&1; then
    die "Docker Compose v2 is required (docker compose)"
fi

[ -f .env ] || die ".env not found — copy .env.example to .env and configure production values first"

APP_ENV="$(read_env APP_ENV local)"
APP_DEBUG="$(read_env APP_DEBUG true)"
APP_URL="$(read_env APP_URL http://localhost:8080)"
HTTP_PORT="$(read_env NGINX_HTTP_PORT 8080)"
DEPLOY_HTTP_PORT="${DEPLOY_HTTP_PORT:-$HTTP_PORT}"

if [ "$APP_ENV" != "production" ]; then
    warn "APP_ENV=$APP_ENV (expected production on deploy servers)"
fi

if [ "$APP_DEBUG" = "true" ] || [ "$APP_DEBUG" = "1" ]; then
    warn "APP_DEBUG is enabled — set APP_DEBUG=false on production"
fi

if [ "$(read_env RUN_SEED false)" = "true" ]; then
    warn "RUN_SEED=true will re-seed on every container start — use only for first bootstrap"
fi

if [ "$APP_ENV" = "production" ] && [ "$(read_env SEED_MODE off)" = "bootstrap" ]; then
    warn "SEED_MODE=bootstrap on production — use SEED_MODE=off after first deploy"
fi

log "Deploying STECI UK Parish from $ROOT_DIR"
log "Branch: $DEPLOY_BRANCH · Compose: $DEPLOY_COMPOSE · URL: $APP_URL"

# --- Git ---------------------------------------------------------------------
if [ "$DO_PULL" -eq 1 ]; then
    if [ -d .git ]; then
        if [ -n "$(git status --porcelain 2>/dev/null)" ] && [ "$FORCE_GIT" -ne 1 ]; then
            die "Git working tree has local changes. Commit, stash, or re-run with --force-git"
        fi

        log "Fetching $DEPLOY_GIT_REMOTE..."
        git fetch "$DEPLOY_GIT_REMOTE" --prune

        if git show-ref --verify --quiet "refs/heads/$DEPLOY_BRANCH"; then
            git checkout "$DEPLOY_BRANCH"
        elif git show-ref --verify --quiet "refs/remotes/$DEPLOY_GIT_REMOTE/$DEPLOY_BRANCH"; then
            git checkout -B "$DEPLOY_BRANCH" "$DEPLOY_GIT_REMOTE/$DEPLOY_BRANCH"
        else
            die "Branch not found: $DEPLOY_BRANCH"
        fi

        log "Pulling $DEPLOY_GIT_REMOTE/$DEPLOY_BRANCH (ff-only)..."
        git pull --ff-only "$DEPLOY_GIT_REMOTE" "$DEPLOY_BRANCH"

        log "Now at: $(git rev-parse --short HEAD) — $(git log -1 --pretty=%s)"
    else
        warn "Not a git repository — skipping git pull"
    fi
else
    log "Skipping git pull (--no-pull)"
fi

# --- Docker build & up -------------------------------------------------------
if [ "$DO_BUILD" -eq 1 ]; then
    log "Building Docker image..."
    $DEPLOY_COMPOSE build --pull
else
    log "Skipping image build (--no-build)"
fi

log "Starting container..."
$DEPLOY_COMPOSE up -d --remove-orphans

# --- Wait for health ---------------------------------------------------------
log "Waiting for /up health check (port $DEPLOY_HTTP_PORT, timeout ${DEPLOY_HEALTH_TIMEOUT}s)..."
TRIES="$DEPLOY_HEALTH_TIMEOUT"
until curl -sf "http://127.0.0.1:${DEPLOY_HTTP_PORT}/up" >/dev/null 2>&1; do
    TRIES=$((TRIES - 1))
    if [ "$TRIES" -le 0 ]; then
        warn "Health check failed — recent logs:"
        $DEPLOY_COMPOSE logs --tail=60 app || true
        die "Container did not become healthy"
    fi
    sleep 1
done
log "Health check passed"

# --- Post-deploy artisan (entrypoint also migrates; this confirms + logs output)
artisan() {
    $DEPLOY_COMPOSE exec -T app php artisan "$@"
}

log "Running migrations..."
artisan migrate --force --no-interaction

log "Ensuring system roles and admin account..."
artisan site:ensure-roles --force --no-interaction
artisan site:ensure-admin --force --no-interaction

if [ "$DO_SYNC" -eq 1 ]; then
    log "Syncing reference data (dev → prod upsert)..."
    artisan site:sync-reference-data --force --no-interaction
fi

if [ "$(read_env DB_CONNECTION sqlite)" = "sqlite" ]; then
    log "Optimizing SQLite..."
    artisan db:optimize-sqlite --no-interaction || true
fi

if [ "$APP_ENV" = "production" ]; then
    log "Caching config/routes/views..."
    artisan optimize --no-interaction
fi

artisan storage:link --force 2>/dev/null || true

# --- Smoke checks ------------------------------------------------------------
log "Smoke testing public site..."
curl -sf "http://127.0.0.1:${DEPLOY_HTTP_PORT}/" | grep -qi "STECI" \
    || die "Homepage smoke test failed"

ADMIN_PATH="$(read_env ADMIN_PATH admin)"
curl -sf "http://127.0.0.1:${DEPLOY_HTTP_PORT}/${ADMIN_PATH}/login" >/dev/null \
    || warn "Admin login page check failed — verify ADMIN_PATH=$ADMIN_PATH"

log "Deploy complete"
echo ""
echo "  Site:  $APP_URL"
echo "  Admin: ${APP_URL%/}/${ADMIN_PATH}"
echo "  Commit: $(git rev-parse --short HEAD 2>/dev/null || echo 'n/a')"
echo ""
if [ "$DO_SYNC" -eq 0 ]; then
    echo "  Tip: run ./scripts/deploy.sh --sync to push dev reference content to prod DB"
fi
