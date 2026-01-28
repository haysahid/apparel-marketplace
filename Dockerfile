# === STAGE 1: Build Frontend Assets (Node.js) ===
FROM php:8.3-fpm-alpine AS builder

# Install build tools, Node, dan dependensi sistem untuk ekstensi PHP
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    postgresql-dev \
    nodejs \
    npm \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    libzip-dev \
    unzip \
    git

# Install ekstensi PHP yang dibutuhkan (Termasuk EXIF untuk Spatie)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql pdo_pgsql bcmath gd zip exif

# Copy Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# 1. Install PHP dependencies (Sekarang exif sudah ada, jadi tidak akan error)
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 2. Build Frontend Assets
RUN npm install
RUN npm run build

# === STAGE 2: Setup Laravel Application (Production) ===
FROM php:8.3-fpm-alpine

WORKDIR /var/www

# Gunakan --update dan pastikan indeks paket segar
RUN apk add --no-cache --update \
    $PHPIZE_DEPS \
    autoconf \
    build-base \
    postgresql-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev \
    zip \
    libzip-dev \
    unzip \
    git \
    curl \
    oniguruma-dev \
    nodejs \
    npm \
    rsync \
    jpegoptim optipng pngquant gifsicle libwebp-tools \
    ghostscript

# Install ekstensi PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install \
       pdo_mysql \
       pdo_pgsql \
       mbstring \
       zip \
       gd \
       bcmath \
       exif \
       fileinfo

# Install Redis secara terpisah untuk menghindari konflik pembersihan apk
RUN pecl install redis && docker-php-ext-enable redis

# Salin folder aplikasi
COPY --from=builder /app /var/www

# BACKUP PUBLIC ASSETS
# Copied to a backup location so entrypoint can sync them to the named volume
RUN mkdir -p /var/www/public_backup && cp -r /var/www/public/* /var/www/public_backup/

# Set Permission
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN chmod +x /var/www/docker-entrypoint.sh

EXPOSE 9000
CMD ["php-fpm"]