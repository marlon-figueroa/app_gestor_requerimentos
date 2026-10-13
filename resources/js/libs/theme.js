/**
 * Modos de color de Bootstrap 5.3: "light", "dark" o "auto" (sigue al sistema operativo).
 * La preferencia se guarda en localStorage; el atributo inicial lo aplica el script
 * en linea de partials/theme-script.blade.php para evitar el destello al cargar.
 */
const STORAGE_KEY = 'theme';
const THEMES = ['light', 'dark', 'auto'];
const media = window.matchMedia('(prefers-color-scheme: dark)');

const ICONS = {
    light: 'bi-sun-fill',
    dark: 'bi-moon-stars-fill',
    auto: 'bi-circle-half',
};

const LABELS = {
    light: 'Claro',
    dark: 'Oscuro',
    auto: 'Automático',
};

export function getStoredTheme() {
    try {
        const theme = localStorage.getItem(STORAGE_KEY);

        return THEMES.includes(theme) ? theme : 'auto';
    } catch {
        return 'auto';
    }
}

function storeTheme(theme) {
    try {
        localStorage.setItem(STORAGE_KEY, theme);
    } catch {
        // Navegacion privada o almacenamiento bloqueado: el tema aplica solo a esta pagina.
    }
}

export function resolveTheme(theme = getStoredTheme()) {
    return theme === 'auto' ? (media.matches ? 'dark' : 'light') : theme;
}

function applyTheme(theme) {
    const resolved = resolveTheme(theme);

    document.documentElement.setAttribute('data-bs-theme', resolved);
    document.dispatchEvent(new CustomEvent('theme:changed', { detail: { theme, resolved } }));
}

function showActiveTheme(theme) {
    document.querySelectorAll('[data-theme-toggle-icon]').forEach((icon) => {
        icon.classList.remove(...Object.values(ICONS));
        icon.classList.add(ICONS[theme]);
    });

    document.querySelectorAll('[data-theme-toggle-button]').forEach((button) => {
        button.setAttribute('aria-label', `Cambiar tema (actual: ${LABELS[theme]})`);
        button.setAttribute('title', `Tema: ${LABELS[theme]}`);
    });

    document.querySelectorAll('[data-bs-theme-value]').forEach((item) => {
        const active = item.dataset.bsThemeValue === theme;

        item.classList.toggle('active', active);
        item.setAttribute('aria-pressed', String(active));
    });
}

export function setTheme(theme) {
    if (!THEMES.includes(theme)) {
        return;
    }

    storeTheme(theme);
    applyTheme(theme);
    showActiveTheme(theme);
}

export function initTheme() {
    applyTheme(getStoredTheme());
    showActiveTheme(getStoredTheme());

    media.addEventListener('change', () => {
        if (getStoredTheme() === 'auto') {
            applyTheme('auto');
        }
    });

    // Sincroniza otras pestanas abiertas del sitio.
    window.addEventListener('storage', (event) => {
        if (event.key === STORAGE_KEY) {
            applyTheme(getStoredTheme());
            showActiveTheme(getStoredTheme());
        }
    });

    document.addEventListener('click', (event) => {
        const item = event.target.closest('[data-bs-theme-value]');

        if (item) {
            event.preventDefault();
            setTheme(item.dataset.bsThemeValue);
        }
    });
}
