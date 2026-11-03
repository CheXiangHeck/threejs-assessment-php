
FROM dunglas/frankenphp:1-php8.4-alpine AS base

WORKDIR /app

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Install Composer dependencies
FROM base AS dependencies

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apk add --no-cache unzip

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --optimize-autoloader

# Production runtime
FROM base AS runtime

COPY --from=dependencies /app/vendor ./vendor

COPY Caddyfile /etc/caddy/Caddyfile

COPY server ./server

# Session storage directory
RUN mkdir -p /var/data/sessions

# Render's default web service port
EXPOSE 10000

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
