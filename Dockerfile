# Base image with PHP, Composer, and NGINX
FROM ghcr.io/serversideup/php:8.3-fpm-nginx

WORKDIR /var/www/html

# Copy composer files and install optimized dependencies
COPY composer.json composer.lock ./
USER root

COPY --chmod=755 ./entrypoint.d/ /etc/entrypoint.d/

RUN mkdir -p bootstrap/cache && \
    chown -R www-data:www-data bootstrap && \
    chmod -R 775 bootstrap/cache && \
    composer install --no-dev --optimize-autoloader --prefer-dist --no-interaction

# Copy rest of the application
COPY . .
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 775 bootstrap/cache

USER www-data
EXPOSE 8080


