---
name: compila-proyecto
description: Recompila las imágenes y levanta los contenedores del proyecto con Podman ejecutando `podman compose up -d --build`. Usar solo cuando el usuario invoque compila-proyecto por nombre.
disable-model-invocation: true
---

# Compilar el proyecto

## Pasos

1. Desde la raíz del repo, ejecutar con permisos completos (Podman necesita salir del sandbox):
   `podman compose up -d --build`
   La compilación puede tardar varios minutos; esperar a que termine.
2. Comprobar el estado: `podman compose ps`. Los servicios `app`, `nginx` y `mysql` deben estar en ejecución.
3. Si `app` no está arriba o se reinicia, revisar `podman compose logs --tail=100 app` (ahí aparecen las migraciones y los seeders).
4. Informar el resultado y la URL http://localhost:8080.

## Errores comunes

- "Cannot connect to Podman" o falla el socket: la máquina virtual está apagada. Indicar al usuario `podman machine start`; no arrancarla sin preguntar.
- Falta `podman/env/app.env` o `podman/env/mysql.env`: indicar `sh podman/setup.sh`.
- Puerto 8080 ocupado: sugerir `APP_PORT=9000 podman compose up -d --build`.

No ejecutar `podman compose down -v` ni `build --no-cache` salvo que el usuario lo pida.
