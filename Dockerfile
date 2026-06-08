# =============================================================================
# STECI UK Parish — Production Docker image
# =============================================================================

# --- Stage 1: Frontend assets ---
FROM node:22-alpine AS frontend

WORKDIR /build

COPY package.json package-lock.json vite.config.js ./
COPY resources ./resources

RUN npm ci --ignore-scripts && npm run build

# --- Stage 2: PHP dependencies ---
FROM composer:2 AS vendor

WORKDIR /build

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# --- Stage 3: PHP-FPM application ---
FROM php:8.4-fpm-alpine AS production

WORKDIR /var/www/html

RUN apk add --no-cache \
        git \
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
    && rm -rf /var/cache/apk/*

COPY docker/php/conf.d/99-laravel.ini /usr/local/etc/php/conf.d/99-laravel.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

COPY . .
COPY --from=vendor /build/vendor ./vendor
COPY --from=frontend /build/public/build ./public/build

RUN mkdir -p storage/database storage/app/public \
        storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && touch storage/database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache public \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm", "-F"]

# --- Stage 4: Nginx with baked public assets ---
FROM nginx:1.27-alpine AS nginx

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=production /var/www/html/public /var/www/html/public

# Symlink storage uploads (mounted at runtime)
RUN mkdir -p /var/www/html/storage/app/public

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
