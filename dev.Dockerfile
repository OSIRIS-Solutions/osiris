# Build-Stage for dependencies
FROM php:8.1-cli AS composer

# Install composer and necessary dependencies
RUN apt-get update && apt-get install -y git unzip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set workdir
WORKDIR /app

# Copy only Composer-Files for better caching
COPY composer.json composer.lock ./
RUN composer install --ignore-platform-reqs --no-interaction --no-dev

# Main-Image
FROM php:8.1-cli

# Author label
LABEL authors="Paul C. Gaida"

# Install mandatory packages und PHP-Extensions to run OSIRIS
RUN apt-get update && apt-get install -y \
    libldap2-dev \
    libzip-dev \
    libssl-dev \
    && docker-php-ext-configure ldap \
    && docker-php-ext-install ldap zip \
    && pecl install mongodb-1.21.0 \
    && docker-php-ext-enable mongodb \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
     && echo "xdebug.discover_client_host=false" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.idekey=PHPSTORM" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.log=/tmp/xdebug.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false \
        build-essential \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

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

# XDebug port
EXPOSE 9003

# Start build in PHP-Server
CMD ["php", "-S", "0.0.0.0:80", "-t", "/var/www/html"]
