# ----------------------------------------
# 👷 STAGE 1: Builder (Install Composer deps & build Laravel)
# ----------------------------------------
FROM php:8.2-fpm-alpine AS builder

RUN apk add --no-cache \
    bash git unzip libzip-dev libpng-dev oniguruma-dev libxml2-dev curl \
    && docker-php-ext-install pdo pdo_mysql zip mbstring xml

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
COPY . .
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs


RUN chmod -R 775 storage bootstrap/cache

# ----------------------------------------
# 🚀 STAGE 2: Runtime (FrankenPHP with MySQL driver)
# ----------------------------------------
FROM dunglas/frankenphp

# ✅ Install dependencies + MySQL PDO extension
RUN apt-get update && apt-get install -y \
    default-mysql-client \
    libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    libicu-dev libonig-dev libxml2-dev zlib1g-dev \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    zip \
    mbstring \
    xml \
    intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY --from=builder /app /app
COPY Caddyfile /etc/frankenphp/Caddyfile

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV FRANKENPHP_CONFIG="worker /app/public/index.php"

EXPOSE 8000
