# === STAGE 1: Build Frontend Assets (Node.js) ===
FROM node:25-alpine AS frontend-builder
WORKDIR /app

# Salin file package untuk install dependencies
COPY package*.json ./
RUN npm install

# Salin seluruh kode dan build aset untuk produksi
COPY . .
RUN npm run build

# === STAGE 2: Setup Laravel Application (PHP) ===
FROM php:8.3-fpm-alpine

# Set working directory
WORKDIR /var/www

# Install system dependencies yang ringan (alpine)
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    autoconf \
    build-base \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    libzip-dev \
    unzip \
    git \
    curl \
    oniguruma-dev

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring zip gd

# Install Redis via PECL
RUN pecl install redis \
    && docker-php-ext-enable redis

# Hapus kembali build tools agar image tetap ringan (optional tapi disarankan)
RUN apk del autoconf build-base $PHPIZE_DEPS

# Ambil Composer terbaru
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Salin seluruh source code Laravel
COPY . .

# Salin hasil build aset dari Stage 1 (Hanya folder public/build)
# Ini yang membuat image jadi sangat ringan karena tidak ada node_modules
COPY --from=frontend-builder /app/public/build ./public/build

# Install PHP dependencies (production mode)
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Set permissions untuk storage dan cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose port untuk PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]

# === STAGE 3: SSR Server (Node.js) ===
FROM node:25-alpine AS ssr-server
WORKDIR /app
COPY . .
RUN npm install --production

EXPOSE 13714
CMD ["node", "ssr.js"]