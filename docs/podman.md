# Levantar el proyecto con Podman

El proyecto incluye un `compose.yaml` en la raíz con tres servicios: `app` (PHP-FPM con Laravel), `nginx` y `mysql`. Todos los comandos se ejecutan desde la raíz del proyecto.

## 1. Preparación (solo la primera vez)

En macOS Podman corre dentro de una máquina virtual, así que primero hay que crearla y encenderla:

```bash
podman machine init      # solo si nunca la has creado
podman machine start
```

`podman compose` necesita un proveedor instalado (`podman-compose` o `docker-compose`). Si no tienes ninguno:

```bash
brew install podman-compose
```

Para generar los archivos de variables de entorno con `APP_KEY` y contraseñas aleatorias:

```bash
sh podman/setup.sh
```

El script no sobrescribe archivos existentes. Si ya existen `podman/env/app.env` y `podman/env/mysql.env`, verifica que `DB_PASSWORD` en `app.env` sea igual a `MYSQL_PASSWORD` en `mysql.env`.

## 2. Compilar y levantar

```bash
# Compilar las imágenes y levantar en segundo plano
podman compose up -d --build

# Solo compilar, sin levantar
podman compose build

# Compilar sin caché (útil si cambiaron dependencias o assets)
podman compose build --no-cache

# Levantar sin recompilar
podman compose up -d
```

Cuando el contenedor `app` arranca, ejecuta solo las migraciones y los seeders. Después, la app queda en <http://localhost:8080> con este usuario:

- **Usuario:** `admin@example.com`
- **Contraseña:** `password`

Si quieres usar otro puerto: `APP_PORT=9000 podman compose up -d`.

## 3. Ver los contenedores

```bash
podman compose ps                # contenedores de este proyecto y su estado
podman ps                        # todos los contenedores en ejecución
podman ps -a                     # incluye los detenidos
podman images | grep gestor      # imágenes compiladas
podman stats                     # uso de CPU y memoria en vivo
```

## 4. Logs y acceso a los contenedores

```bash
podman compose logs -f           # logs de todos los servicios
podman compose logs -f app       # solo Laravel (migraciones, errores)
podman compose logs -f nginx
podman compose logs -f mysql

podman compose exec app sh                          # shell dentro de la app
podman compose exec app php artisan migrate:status  # comandos de artisan
podman compose exec mysql mysql -u gestor -p app_gestor_requerimentos
```

MySQL también está expuesto en tu máquina en `127.0.0.1:3307` (configurable con `DB_HOST_PORT`), por si quieres conectarte con TablePlus, DBeaver u otro cliente.

## 5. Detener y limpiar

```bash
podman compose stop              # detiene los contenedores sin borrarlos
podman compose start             # los vuelve a arrancar
podman compose restart app       # reinicia un servicio
podman compose down              # detiene y elimina los contenedores (los datos se conservan)
podman compose down -v           # además borra los volúmenes: se pierde la BD y el storage
```

Si cambias código PHP, vistas o assets, tienes que recompilar con `podman compose up -d --build`. El código se copia dentro de la imagen; no está montado como volumen, así que los cambios no se reflejan en caliente.
