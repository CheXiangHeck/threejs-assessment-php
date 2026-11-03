FROM dunglas/frankenphp:1-php8.4

WORKDIR /app

# Use production PHP configuration
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Install system dependencies and PHP extensions
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        unzip \
        libzip-dev \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy Composer files first so Docker can cache this layer
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

# Create session directory
RUN mkdir -p /var/data/sessions

# Render port
ENV PORT=10000

EXPOSE 10000

# Start FrankenPHP
ENTRYPOINT ["/usr/local/bin/frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]