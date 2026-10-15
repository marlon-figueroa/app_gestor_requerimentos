# Imagen de producción: Laravel + Apache + SQLite (Render u otro host Docker).
# Desarrollo local sigue usando Podman + MySQL (compose.yaml).

ARG NODE_VERSION=22

FROM docker.io/library/node:${NODE_VERSION}-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

FROM docker.io/library/php:8.4-apache-bookworm AS app

COPY --from=docker.io/mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions bcmath exif gd intl opcache pdo_sqlite zip \
    && a2enmod rewrite headers \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

COPY --from=docker.io/library/composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress

COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-dev --no-interaction \
    && mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache public/build

COPY deploy/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
