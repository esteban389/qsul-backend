# Base image with PHP, Composer, and NGINX
FROM ghcr.io/serversideup/php:8.3-fpm-nginx

WORKDIR /var/www/html

# Copy composer files and install optimized dependencies
COPY composer.json composer.lock ./

# Copy rest of the application
COPY . .

# Fix ownership and permissions AFTER copying the full app
USER root

COPY --chmod=755 ./entrypoint.d/ /etc/entrypoint.d/

RUN rm -rf vendor && \
    apt-get update && apt-get install -y git \
    && rm -rf /var/lib/apt/lists/* && \
    mkdir -p bootstrap/cache && \
    chown -R www-data:www-data bootstrap && \
    chmod -R 775 bootstrap/cache && \
    composer install --no-dev --optimize-autoloader --prefer-dist --no-interaction

RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 775 bootstrap/cache

# Going back to www-data user
USER www-data

#Expose port
EXPOSE 8080


