#!/bin/sh
set -e

cd /var/www/html

PORT="${PORT:-80}"

if [ "$PORT" != "80" ]; then
    sed -i "s/^Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
    sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/*.conf
    sed -i "s/:80 /:${PORT} /" /etc/apache2/sites-available/*.conf
fi

mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

artisan() {
    su -s /bin/sh www-data -c "php artisan $*"
}

if [ ! -e public/storage ]; then
    artisan storage:link >/dev/null 2>&1 || true
fi

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    db_path="${DB_DATABASE:-/var/www/html/storage/app/database.sqlite}"
    db_dir=$(dirname "$db_path")
    mkdir -p "$db_dir"
    if [ ! -f "$db_path" ]; then
        touch "$db_path"
    fi
    chown -R www-data:www-data "$db_dir"
fi

if [ "${APP_RUN_MIGRATIONS:-true}" = "true" ]; then
    if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
        artisan migrate --force --no-interaction
    else
        intentos=0
        until artisan migrate --force --no-interaction; do
            intentos=$((intentos + 1))
            if [ "$intentos" -ge 20 ]; then
                echo "No fue posible ejecutar las migraciones." >&2
                exit 1
            fi
            echo "Base de datos no disponible, reintentando en 3 s ($intentos/20)..."
            sleep 3
        done
    fi
fi

if [ "${APP_RUN_SEEDERS:-false}" = "true" ]; then
    artisan db:seed --force --no-interaction
else
    vacio=$(su -s /bin/sh www-data -c "php -r \"require 'vendor/autoload.php'; \\\$app = require 'bootstrap/app.php'; \\\$app->make(Illuminate\\\\Contracts\\\\Console\\\\Kernel::class)->bootstrap(); echo App\\\\Models\\\\User::query()->count();\"" 2>/dev/null || echo "1")
    if [ "$vacio" = "0" ]; then
        artisan db:seed --force --no-interaction
    fi
fi

artisan optimize:clear >/dev/null 2>&1 || true
artisan config:cache
artisan route:cache
artisan view:cache

chown -R www-data:www-data storage bootstrap/cache

exec apache2-foreground
