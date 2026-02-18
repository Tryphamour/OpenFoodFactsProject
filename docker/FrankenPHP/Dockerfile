FROM dunglas/frankenphp:1-php8.4

RUN install-php-extensions pdo_pgsql
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY docker/FrankenPHP/Caddyfile /etc/frankenphp/Caddyfile

WORKDIR /app
