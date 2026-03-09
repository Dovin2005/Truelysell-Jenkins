FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev \
    libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring xml zip bcmath

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

# 🔥 Create ALL required Laravel directories BEFORE composer install
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

# 🔥 Give permission BEFORE composer install
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# 🔥 Important: disable scripts during build
RUN composer install --no-dev --optimize-autoloader --no-scripts

CMD ["php-fpm"]
