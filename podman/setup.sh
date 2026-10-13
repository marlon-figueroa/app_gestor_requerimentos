#!/bin/sh
# Genera podman/env/app.env y podman/env/mysql.env con APP_KEY y contraseñas
# aleatorias. No sobrescribe archivos existentes.
set -e

dir="$(cd "$(dirname "$0")" && pwd)/env"

aleatorio() {
    openssl rand -base64 48 | tr -dc 'A-Za-z0-9' | cut -c1-32
}

if [ -f "$dir/app.env" ] || [ -f "$dir/mysql.env" ]; then
    echo "Ya existen archivos en $dir; no se modifican."
    exit 0
fi

db_password="$(aleatorio)"
root_password="$(aleatorio)"
app_key="base64:$(openssl rand -base64 32)"

sed -e "s|^APP_KEY=.*|APP_KEY=$app_key|" \
    -e "s|^DB_PASSWORD=.*|DB_PASSWORD=$db_password|" \
    "$dir/app.env.example" > "$dir/app.env"

sed -e "s|^MYSQL_PASSWORD=.*|MYSQL_PASSWORD=$db_password|" \
    -e "s|^MYSQL_ROOT_PASSWORD=.*|MYSQL_ROOT_PASSWORD=$root_password|" \
    "$dir/mysql.env.example" > "$dir/mysql.env"

chmod 600 "$dir/app.env" "$dir/mysql.env"
echo "Generados $dir/app.env y $dir/mysql.env"
