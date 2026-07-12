FROM php:8.3-cli

# Extensiones de PHP requeridas por Laravel + MySQL (pdo_mysql) y utilidades
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-install \
        pdo_mysql \
        bcmath \
        intl \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

# Sin dependencias de desarrollo (Faker NO queda en la imagen: por eso los
# seeders de producción no dependen de factories).
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 10000

# Al arrancar: limpiar config, migrar (idempotente) y servir.
# El seeding NO va aquí: se corre UNA sola vez desde la Shell de Render con
#   php artisan db:seed --force
# (varios seeders usan insert() y se duplicarían en cada reinicio del plan Free).
CMD ["sh", "-c", "php artisan config:clear && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]
