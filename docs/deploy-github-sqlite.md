# Despliegue con GitHub Actions, GitHub Pages y Render (SQLite)

## Qué va dónde

| Componente | Dónde | Costo habitual |
|------------|--------|----------------|
| **GitHub Pages** | Landing en `docs/` (solo HTML) | Gratis |
| **GitHub Actions** | CI (tests) + deploy Render + publicar Pages | Gratis con límites |
| **Render (Free)** | Aplicación Laravel + SQLite | Gratis (con limitaciones) |

**GitHub Pages no ejecuta PHP.** La app corre en Render usando [`deploy/Dockerfile`](../deploy/Dockerfile).

---

## 1. Render (aplicación Laravel)

1. Crea cuenta en [Render](https://render.com).
2. **New → Blueprint** y conecta este repositorio (usa [`render.yaml`](../render.yaml) en la raíz), o **New Web Service** con Docker:
   - Si la URL `https://gestor-requerimientos.onrender.com` responde **404**, el servicio **aún no existe** en tu cuenta: hay que crearlo con Blueprint o Web Service; el deploy hook de GitHub **no crea** el servicio solo.
   - Dockerfile: `deploy/Dockerfile`
   - Context: raíz del repo
   - Plan: **Free**
3. Variables de entorno (ver [`deploy/env.production.example`](../deploy/env.production.example)):
   - `APP_KEY`: genera con `php artisan key:generate --show` en local
   - `APP_URL`: URL que te asigne Render (https://….onrender.com)
4. Tras el primer deploy, entra a la URL y prueba login con usuarios seed.

### Deploy automático desde GitHub

1. En Render: servicio → **Settings → Deploy Hook** → copia la URL.
2. En GitHub: **Settings → Secrets and variables → Actions** → `RENDER_DEPLOY_HOOK` = esa URL.
3. Cada push a `main` dispara [`.github/workflows/deploy-render.yml`](../.github/workflows/deploy-render.yml) (si el secret existe).

---

## 2. GitHub Pages (landing)

1. GitHub → **Settings → Pages** → Build and deployment → Source: **GitHub Actions** (obligatorio la primera vez; el workflow también intenta activarlo con `enablement: true`).
   - Si falla con *Get Pages site failed*, casi siempre falta este paso o el repo no tiene permisos de Pages en el plan de la org.
2. **Variables** → `DEMO_APP_URL` = URL real de tu servicio Render (sin `/` final). Sin esta variable, la landing no muestra el botón «Abrir aplicación» y enlaza a [configurar-render.html](configurar-render.html).
3. El workflow [`.github/workflows/pages.yml`](../.github/workflows/pages.yml) publica `docs/` en cada push a `main`.

---

## 3. CI

[`.github/workflows/ci.yml`](../.github/workflows/ci.yml) ejecuta `composer install`, `npm run build` y `php artisan test` en cada PR y push.

---

## Limitaciones del tier gratuito (Render)

- El servicio **se duerme** sin tráfico (arranque lento).
- Disco **efímero**: SQLite y fotos pueden **perderse** al redeploy.
- No usar para datos de producción reales sin plan de pago o backups.

---

## Desarrollo local

Sigue usando Podman + MySQL: [`docs/podman.md`](podman.md).
