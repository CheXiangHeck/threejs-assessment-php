
FROM dunglas/frankenphp:1-php8.4

WORKDIR /app

# Production PHP configuration
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Install Composer dependencies
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --optimize-autoloader

# Copy application dependencies and source files
COPY Caddyfile /etc/caddy/Caddyfile
COPY server ./server

# Create session storage directory
RUN mkdir -p /var/data/sessions

# Render port
ENV PORT=10000

EXPOSE 10000

# Override the default entrypoint and start FrankenPHP directly
ENTRYPOINT ["/usr/local/bin/frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
