# =============================================================================
# STECI UK Parish — minimal single-container image
# Node + Composer are build-only stages; runtime is PHP + nginx + supervisord.
# =============================================================================

# --- Build: Vite / Tailwind → public/build ---
FROM node:22-alpine AS frontend

WORKDIR /build

COPY package.json package-lock.json vite.config.js ./
COPY resources ./resources

RUN npm ci --ignore-scripts \
    && npm run build \
    && rm -rf node_modules

# --- Build: PHP dependencies ---
FROM composer:2 AS vendor

WORKDIR /build

COPY composer.json composer.lock ./
COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY public ./public
COPY resources ./resources
COPY routes ./routes
COPY artisan ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader \
    --classmap-authoritative

# --- Runtime ---
FROM php:8.4-fpm-alpine AS production

WORKDIR /var/www/html

RUN apk add --no-cache \
        nginx \
        supervisor \
        sqlite \
        libzip \
        libpng \
        libjpeg-turbo \
        freetype \
        oniguruma \
        libxml2 \
        icu-libs \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
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
        pcntl \
        bcmath \
        gd \
        intl \
        opcache \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* /etc/nginx/http.d/default.conf /usr/src/php*

COPY docker/php/conf.d/99-laravel.ini /usr/local/etc/php/conf.d/99-laravel.ini
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

COPY app bootstrap config database public resources routes artisan composer.json composer.lock ./
COPY --from=vendor /build/vendor ./vendor
COPY --from=frontend /build/public/build ./public/build

# Strip noise from vendor; warm Laravel caches for production boots
RUN find vendor -type d -name tests -prune -exec rm -rf {} + 2>/dev/null || true \
    && find vendor -type d -name Tests -prune -exec rm -rf {} + 2>/dev/null || true \
    && find vendor -name '*.md' -delete 2>/dev/null || true \
    && find vendor -name 'phpunit.xml*' -delete 2>/dev/null || true \
    && mkdir -p storage/database storage/app/public storage/app/private \
        storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && touch storage/database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache public \
    && chmod -R 775 storage bootstrap/cache

ENV RUN_QUEUE_WORKER=false
ENV RUN_SCHEDULER=true

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --retries=3 --start-period=45s \
    CMD wget -qO- http://127.0.0.1/up > /dev/null 2>&1 || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
