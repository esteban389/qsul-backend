# Base image with PHP, Composer, and NGINX
FROM ghcr.io/serversideup/php:8.3-fpm-nginx

WORKDIR /var/www/html

# Copy composer files and install optimized dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-interaction

# Copy rest of the application
COPY . .

EXPOSE 8080


