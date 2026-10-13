/**
 * Tema claro u oscuro (Bootstrap 5.3 data-bs-theme). La preferencia va en localStorage;
 * el script en partials/theme-script.blade.php aplica el valor inicial sin destello.
 * "auto" en almacenamiento antiguo se interpreta como el tema del sistema hasta que el usuario alterna.
 */
const STORAGE_KEY = 'theme';
const THEMES = ['light', 'dark', 'auto'];
const media = window.matchMedia('(prefers-color-scheme: dark)');

const ICONS = {
    light: 'bi-sun-fill',
    dark: 'bi-moon-stars-fill',
};

const LABELS = {
    light: 'Claro',
    dark: 'Oscuro',
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

function showActiveTheme(theme = getStoredTheme()) {
    const resolved = resolveTheme(theme);
    const iconClass = ICONS[resolved];

    document.querySelectorAll('[data-theme-toggle-icon]').forEach((icon) => {
        icon.classList.remove(ICONS.light, ICONS.dark);
        icon.classList.add(iconClass);
    });

    document.querySelectorAll('[data-theme-toggle-button]').forEach((button) => {
        const siguiente = resolved === 'dark' ? 'light' : 'dark';

        button.setAttribute('aria-label', `Tema ${LABELS[resolved]}. Cambiar a ${LABELS[siguiente]}.`);
        button.setAttribute('title', `Tema ${LABELS[resolved]}`);
        button.setAttribute('aria-pressed', resolved === 'dark' ? 'true' : 'false');
    });
}

function toggleTheme() {
    setTheme(resolveTheme() === 'dark' ? 'light' : 'dark');
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
        const button = event.target.closest('[data-theme-toggle-button]');

        if (button) {
            event.preventDefault();
            toggleTheme();
        }
    });
}
