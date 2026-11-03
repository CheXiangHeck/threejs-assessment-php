FROM dunglas/frankenphp:1-php8.4

WORKDIR /app

# Production PHP configuration
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Install dependencies required by Composer
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        unzip \
        libzip-dev \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy Composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --optimize-autoloader

# Copy application
COPY Caddyfile /etc/caddy/Caddyfile
COPY server ./server

# Session storage
RUN mkdir -p /var/data/sessions

# Render uses PORT
ENV PORT=10000

EXPOSE 10000

# Do NOT specify ENTRYPOINT.
# FrankenPHP's image already provides its own entrypoint.
CMD ["run", "--config", "/etc/caddy/Caddyfile"]