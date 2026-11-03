
# Base image
FROM dunglas/frankenphp:1-php8.4-alpine AS base

WORKDIR /app

# Use production PHP configuration
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Install dependencies
FROM base AS dependencies

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apk add --no-cache unzip

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --optimize-autoloader

# Runtime image
FROM base AS runtime

# Copy Composer dependencies
COPY --from=dependencies /app/vendor ./vendor

# Copy Caddy configuration
COPY Caddyfile /etc/caddy/Caddyfile

# Copy PHP application
COPY server ./server

# Railway configuration
ENV PORT=8080

# PHP session configuration
RUN mkdir -p /data/sessions

# Expose application port
EXPOSE 8080

# Start FrankenPHP
CMD ["sh", "-c", "frankenphp run --config /etc/caddy/Caddyfile --adapter caddyfile"]
