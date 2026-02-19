FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev \
    libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring xml zip bcmath

# 🔥 Install Xdebug for PHPUnit coverage
RUN pecl install xdebug \
 && docker-php-ext-enable xdebug

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy project files
COPY . .

# 🔥 Install dependencies WITH dev (needed for phpunit)
RUN composer install --optimize-autoloader

# Ensure Laravel writable folders exist + permissions
RUN mkdir -p storage bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

CMD ["php-fpm"]
