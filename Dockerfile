# === STAGE 1: Build Frontend Assets (Node.js) ===
# We need PHP in the build stage because Vite requires files inside /vendor
FROM php:8.3-fpm-alpine AS builder

# Install system dependencies needed for PHP & Node
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    nodejs \
    npm \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    libzip-dev \
    unzip \
    git

# Copy Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# 1. Install PHP dependencies FIRST (So the vendor/ziggy folder is available for Vite)
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 2. Build Frontend Assets
RUN npm install
RUN npm run build

# === STAGE 2: Setup Laravel Application (Production) ===
FROM php:8.3-fpm-alpine

WORKDIR /var/www

# Still use your chosen system dependencies and PHP extensions
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
    nodejs \
    npm \
    && docker-php-ext-install pdo_mysql mbstring zip gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del autoconf build-base $PHPIZE_DEPS

# Copy all results from builder (including vendor and public/build)
COPY --from=builder /app /var/www

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]