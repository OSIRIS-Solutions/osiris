# Build-Stage for dependencies
FROM php:8.1-fpm-alpine AS composer

# Install composer and necessary dependencies
RUN apk add --no-cache git unzip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set workdir
WORKDIR /app

# Copy only Composer-Files for better caching
COPY composer.json composer.lock ./
RUN composer install --ignore-platform-reqs --no-interaction --no-dev

# Main-Image
FROM php:8.1-fpm-alpine

# Author label
LABEL authors="Paul C. Gaida"

# Install mandatory packages und PHP-Extensions to run OSIRIS
RUN apk update && apk add --no-cache \
    openldap-dev \
    libzip-dev \
    openssl-dev \
    autoconf \
    gcc \
    g++ \
    make \
    && docker-php-ext-configure ldap \
    && docker-php-ext-install ldap zip \
    && pecl install mongodb-1.21.0 \
    && docker-php-ext-enable mongodb

# Set workdir
WORKDIR /var/www/html

# Copy dependencies from builder
COPY --from=composer /app/vendor ./vendor

# Copy OSIRIS source code
COPY . .

# Set permissions for img-Directory
RUN mkdir -p /var/www/html/img && \
    chown -R www-data:www-data /var/www/html/img && \
    chmod -R 775 /var/www/html/img
