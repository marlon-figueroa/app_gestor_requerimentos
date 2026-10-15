#!/bin/sh
set -e

cd /var/www/html

mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

if [ ! -e public/storage ]; then
    php artisan storage:link >/dev/null 2>&1 || true
fi

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    db_path="${DB_DATABASE:-/var/www/html/storage/app/database.sqlite}"
    db_dir=$(dirname "$db_path")
    mkdir -p "$db_dir"
    if [ ! -f "$db_path" ]; then
        touch "$db_path"
        chown www-data:www-data "$db_path"
    fi
fi

if [ "${APP_RUN_MIGRATIONS:-true}" = "true" ]; then
    if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
        php artisan migrate --force --no-interaction
    else
        intentos=0
        until php artisan migrate --force --no-interaction; do
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
    php artisan db:seed --force --no-interaction
else
    vacio=$(php -r "require 'vendor/autoload.php'; \$app = require 'bootstrap/app.php'; \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); echo App\Models\User::query()->count();" 2>/dev/null || echo "1")
    if [ "$vacio" = "0" ]; then
        php artisan db:seed --force --no-interaction
    fi
fi

php artisan optimize:clear >/dev/null 2>&1 || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
