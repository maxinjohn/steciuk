# =============================================================================
# STECI UK Parish — Single-container Docker image
#
# Build stages use Node (frontend) and Composer (PHP deps) — neither is in the
# final runtime image. Production runs nginx + PHP-FPM + queue + scheduler
# via supervisord in one container.
# =============================================================================

# --- Stage 1: Frontend assets (Node — build only, not shipped) ---
FROM node:22-alpine AS frontend

WORKDIR /build

COPY package.json package-lock.json vite.config.js ./
COPY resources ./resources

RUN npm ci --ignore-scripts && npm run build

# --- Stage 2: PHP dependencies (Composer — build only) ---
FROM composer:2 AS vendor

WORKDIR /build

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# --- Stage 3: Runtime (nginx + PHP-FPM + supervisord) ---
FROM php:8.4-fpm-alpine AS production

WORKDIR /var/www/html

RUN apk add --no-cache \
        nginx \
        supervisor \
        curl \
        zip \
        unzip \
        sqlite \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        oniguruma-dev \
        libxml2-dev \
        icu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_sqlite \
        mbstring \
        zip \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        opcache \
    && apk del --no-cache libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev oniguruma-dev libxml2-dev icu-dev \
    && rm -rf /var/cache/apk/* /etc/nginx/http.d/default.conf

COPY docker/php/conf.d/99-laravel.ini /usr/local/etc/php/conf.d/99-laravel.ini
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

COPY . .
COPY --from=vendor /build/vendor ./vendor
COPY --from=frontend /build/public/build ./public/build

RUN mkdir -p storage/database storage/app/public storage/app/private \
        storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && touch storage/database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache public \
    && chmod -R 775 storage bootstrap/cache

ENV RUN_QUEUE_WORKER=true
ENV RUN_SCHEDULER=true

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=10s --retries=3 --start-period=60s \
    CMD curl -fsS http://127.0.0.1/up > /dev/null || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
