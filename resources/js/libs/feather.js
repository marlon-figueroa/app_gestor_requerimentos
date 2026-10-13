import feather from 'feather-icons';

/**
 * Feather Icons: convierte <i data-feather="nombre"></i> en SVG.
 * Un MutationObserver procesa tambien el contenido agregado despues de la carga
 * (filas de DataTables, modales, resultados de Select2, etc.).
 */
const ATTRS = { 'aria-hidden': 'true', 'stroke-width': 2 };

export function refreshFeatherIcons() {
    if (document.querySelector('[data-feather]')) {
        feather.replace(ATTRS);
    }
}

export function initFeatherIcons() {
    refreshFeatherIcons();

    let pending = false;

    new MutationObserver(() => {
        if (pending) {
            return;
        }

        pending = true;
        requestAnimationFrame(() => {
            pending = false;
            refreshFeatherIcons();
        });
    }).observe(document.body, { childList: true, subtree: true });
}

export { feather };
