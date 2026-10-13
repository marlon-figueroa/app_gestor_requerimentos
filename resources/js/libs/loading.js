import { twinOrbitMarkup } from './twin-orbit';

const SKIP_HEADER = 'X-Skip-Global-Loading';

let overlay = null;
let refCount = 0;

function shouldSkip(config) {
    const headers = config.headers ?? {};

    return headers[SKIP_HEADER] === true
        || headers[SKIP_HEADER] === 'true'
        || headers.common?.[SKIP_HEADER] === true
        || headers.common?.[SKIP_HEADER] === 'true';
}

function ensureOverlay() {
    if (overlay) {
        return overlay;
    }

    overlay = document.createElement('div');
    overlay.className = 'app-loading-overlay';
    overlay.setAttribute('aria-hidden', 'true');
    overlay.innerHTML = `
        <div class="app-loading-overlay__panel text-primary">
            ${twinOrbitMarkup({ label: 'Cargando…', size: 'lg' })}
            <p class="app-loading-overlay__label" aria-hidden="true">Cargando…</p>
        </div>
    `.trim();
    document.body.appendChild(overlay);

    return overlay;
}

export function showLoading() {
    refCount += 1;
    const element = ensureOverlay();
    element.classList.add('is-active');
    element.setAttribute('aria-hidden', 'false');
}

export function hideLoading() {
    refCount = Math.max(0, refCount - 1);

    if (refCount === 0 && overlay) {
        overlay.classList.remove('is-active');
        overlay.setAttribute('aria-hidden', 'true');
    }
}

export function initGlobalLoading() {
    const axios = window.axios;

    if (!axios?.interceptors) {
        return;
    }

    axios.interceptors.request.use((config) => {
        if (shouldSkip(config)) {
            config.skipGlobalLoading = true;
        } else {
            showLoading();
        }

        return config;
    });

    axios.interceptors.response.use(
        (response) => {
            if (!response.config?.skipGlobalLoading) {
                hideLoading();
            }

            return response;
        },
        (error) => {
            if (!error.config?.skipGlobalLoading) {
                hideLoading();
            }

            return Promise.reject(error);
        },
    );
}
