/**
 * Boton de menu del topbar: en escritorio (>= lg) colapsa/expande el sidebar y recuerda
 * la preferencia; en pantallas pequenas abre el sidebar como offcanvas.
 */
const STORAGE_KEY = 'sidebar';
const desktop = window.matchMedia('(min-width: 992px)');

function storeCollapsed(collapsed) {
    try {
        localStorage.setItem(STORAGE_KEY, collapsed ? 'collapsed' : 'expanded');
    } catch {
        // Almacenamiento no disponible: el estado dura solo en esta pagina.
    }
}

function updateToggles() {
    const expanded = desktop.matches
        ? !document.documentElement.classList.contains('sidebar-collapsed')
        : document.getElementById('app-sidebar')?.classList.contains('show') ?? false;

    document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
        button.setAttribute('aria-expanded', String(expanded));
    });
}

export function initSidebar() {
    const sidebar = document.getElementById('app-sidebar');

    if (!sidebar) {
        return;
    }

    document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            if (desktop.matches) {
                const collapsed = document.documentElement.classList.toggle('sidebar-collapsed');
                storeCollapsed(collapsed);
            } else {
                window.bootstrap.Offcanvas.getOrCreateInstance(sidebar).toggle();
            }

            updateToggles();
        });
    });

    sidebar.addEventListener('shown.bs.offcanvas', updateToggles);
    sidebar.addEventListener('hidden.bs.offcanvas', updateToggles);

    desktop.addEventListener('change', () => {
        if (desktop.matches) {
            window.bootstrap.Offcanvas.getInstance(sidebar)?.hide();
        }

        updateToggles();
    });

    updateToggles();
}
