FROM php:8.4-apache

WORKDIR /var/www/html

# Install required PHP extensions and utilities
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        unzip \
        libzip-dev \
    && docker-php-ext-install zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install PHP dependencies
COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --optimize-autoloader

# Copy PHP application
COPY server/ ./

# Create persistent session directory
RUN mkdir -p /var/data/sessions \
    && chown -R www-data:www-data /var/data/sessions /var/www/html

# Apache normally uses port 80.
# Render expects the application to listen on port 10000.
RUN sed -ri 's/Listen 80/Listen 10000/g' /etc/apache2/ports.conf \
    && sed -ri 's/<VirtualHost \*:80>/<VirtualHost *:10000>/g' \
        /etc/apache2/sites-available/000-default.conf

EXPOSE 10000

CMD ["apache2-foreground"]