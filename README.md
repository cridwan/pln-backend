# PLN IP UBH

Aplikasi monitoring project untuk PLN IP

## Persiapan

-   php >= 8.3
-   mysql
-   minio
-   composer
-   docker

## Cara Install

clone project dari github

```bash
git clone https://github.com/cridwan/pln-backend.git
```

setelah clone lalu running ubah directory ke pln-backend

```bash
cd pln-backend
```

lalu install dependencies

```bash
composer install
```

lalu copy file .env.example jadi .env

```bash
cp .env.example .env
```

configurasi database sesuai dengan lokal masing masing di .env
master

```bash
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=tensor_global
DB_USERNAME=your-mysql-username
DB_PASSWORD=your-mysql-password
```

transaksi

```bash
SECOND_DB_CONNECTION=transaction
SECOND_DB_HOST=mysql
SECOND_DB_PORT=3306
SECOND_DB_DATABASE=tensor_transaction
SECOND_DB_USERNAME=your-mysql-username
SECOND_DB_PASSWORD=your-mysql-password
```

document

```bash
DOCUMENT_DB_CONNECTION=document
DOCUMENT_DB_HOST=mysql
DOCUMENT_DB_PORT=3306
DOCUMENT_DB_DATABASE=tensor_document
DOCUMENT_DB_USERNAME=your-mysql-username
DOCUMENT_DB_PASSWORD=your-mysql-password
```

generate key

```bash
php artisan key:generate
```

migrate schema migration

master

```bash
composer migrate:master:fresh
```

transaksi

```bash
composer migrate:transaction:fresh
```

document

```bash
composer migrate:document:fresh
```

generate key passport

```bash
php artisan passport:keys
php artisan passport:client --personal
```

running & build docker

```bash
docker compose --env-file .env.docker build
docker compose --env-file .env.docker up -d
```

## GD ISSUE

Please install gd extension

```bash
docker exec -it nama-container sh
```

```bash
apk add --no-cache \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libxpm-dev
```

```bash
docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp \
    --with-xpm
```

```bash
docker-php-ext-install gd
```

```bash
php -m | grep gd
```
