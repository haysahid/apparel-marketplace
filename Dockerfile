# === STAGE 1: Build Frontend Assets (Node.js) ===
FROM node:25-alpine AS frontend-builder
WORKDIR /app

# Copy package files and install dependencies
COPY package*.json ./
RUN npm install

# Copy all source code and build production assets
COPY . .
RUN npm run build

# === STAGE 2: Setup Laravel Application (PHP) ===
FROM php:8.3-fpm-alpine

# Set working directory
WORKDIR /var/www

# Install lightweight system dependencies (alpine), PHP extensions, and Redis via PECL
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
    oniguruma-dev \
    && docker-php-ext-install pdo_mysql mbstring zip gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del autoconf build-base $PHPIZE_DEPS

# Copy latest Composer from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy all Laravel source code
COPY . .

# Copy built frontend assets from Stage 1 (only public/build folder)
COPY --from=frontend-builder /app/public/build ./public/build

# Install PHP dependencies (production mode)
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Set permissions for storage and cache directories
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose port for PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
