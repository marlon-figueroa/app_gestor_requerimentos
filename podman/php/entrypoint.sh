#!/bin/sh
set -e

cd /var/www/html

artisan() {
    su-exec www-data php artisan "$@"
}

# El volumen de storage puede llegar vacío o con otro dueño.
mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

if [ ! -e public/storage ]; then
    artisan storage:link >/dev/null
fi

if [ "$1" = "php-fpm" ]; then
    if [ "${APP_RUN_MIGRATIONS:-true}" = "true" ]; then
        intentos=0
        until artisan migrate --force --no-interaction; do
            intentos=$((intentos + 1))
            if [ "$intentos" -ge 20 ]; then
                echo "No fue posible ejecutar las migraciones; revise la conexión a MySQL." >&2
                exit 1
            fi
            echo "MySQL aún no está disponible, reintentando en 3 s ($intentos/20)..."
            sleep 3
        done
    fi

    if [ "${APP_RUN_SEEDERS:-true}" = "true" ]; then
        artisan db:seed --force --no-interaction
    fi

    artisan optimize:clear >/dev/null
    artisan optimize
fi

exec docker-php-entrypoint "$@"
