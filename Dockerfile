# Stage 1: Builder untuk Laravel
FROM php:8.2-fpm-alpine AS builder

# Install dependencies di Alpine
RUN apk add --no-cache \
    bash git unzip libzip-dev libpng-dev oniguruma-dev libxml2-dev curl \
    && docker-php-ext-install pdo pdo_mysql zip mbstring xml

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy Laravel project
COPY . .

# Install Laravel dependencies
RUN composer install --ignore-platform-reqs --no-dev --optimize-autoloader

# Set permission
RUN chmod -R 775 storage bootstrap/cache

# Stage 2: Runtime dengan FrankenPHP
FROM dunglas/frankenphp

# Install ekstensi MySQL & dependensinya
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    zlib1g-dev \
    curl \
    git \
    unzip \
    default-mysql-client \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    zip \
    mbstring \
    xml \
    intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copy Laravel hasil build dari stage sebelumnya
COPY --from=builder /app /app

# Copy FrankenPHP config
COPY Caddyfile /etc/frankenphp/Caddyfile

EXPOSE 8000

ENV FRANKENPHP_CONFIG="worker ./public/index.php"
