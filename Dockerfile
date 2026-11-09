# VinzyPlay — image Docker produksi.
#
# Tiga tahap:
#   1. vendor : dependensi Composer (dipakai juga oleh Tailwind untuk memindai view paginasi)
#   2. assets : build CSS/JS dengan Vite
#   3. app    : FrankenPHP (web server + PHP dalam satu proses) yang menyajikan /app/public
#
# Kredensial tidak ditanam ke image; semuanya dibaca dari .env.production saat runtime.

# --- 1. Dependensi Composer ------------------------------------------------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --no-interaction \
    --ignore-platform-reqs

# --- 2. Aset frontend ------------------------------------------------------
FROM node:22-bookworm-slim AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY vite.config.js ./
COPY resources ./resources
COPY --from=vendor /app/vendor/laravel/framework/src/Illuminate/Pagination/resources/views \
     ./vendor/laravel/framework/src/Illuminate/Pagination/resources/views
RUN npm run build

# --- 3. Aplikasi -----------------------------------------------------------
FROM dunglas/frankenphp:1-php8.4-bookworm

# pdo_pgsql untuk Neon, gd untuk logo di PDF struk (dompdf), intl untuk format tanggal.
RUN install-php-extensions \
    pdo_pgsql \
    intl \
    zip \
    gd \
    bcmath \
    opcache \
    pcntl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php.ini /usr/local/etc/php/conf.d/99-vinzyplay.ini

WORKDIR /app

COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY --from=assets /app/public/build ./public/build

# storage/ dibuang lewat .dockerignore, jadi direktori kerjanya dibuat ulang di sini.
# Tanpa storage/framework/views, artisan gagal dengan "Please provide a valid cache path".
RUN mkdir -p \
        storage/logs \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/app/public \
        bootstrap/cache \
        public/images/products/uploads \
        public/images/reviews \
    && composer dump-autoload --optimize --no-dev --classmap-authoritative \
    && php artisan package:discover --ansi \
    && chmod -R ug+rw storage bootstrap/cache public/images/products/uploads public/images/reviews

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
