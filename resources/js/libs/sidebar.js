/**
 * Boton de menu del topbar: en escritorio (>= lg) estrecha el sidebar (solo iconos) o lo
 * expande, y recuerda la preferencia; en pantallas pequenas abre el sidebar como offcanvas.
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

function isCollapsedDesktop() {
    return desktop.matches && document.documentElement.classList.contains('sidebar-collapsed');
}

function closeFlyouts() {
    document.querySelectorAll('.app-sidebar-flyout-open').forEach((item) => {
        item.classList.remove('app-sidebar-flyout-open');
    });
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
                if (! collapsed) {
                    closeFlyouts();
                }
            } else {
                window.bootstrap.Offcanvas.getOrCreateInstance(sidebar).toggle();
            }

            updateToggles();
        });
    });

    sidebar.addEventListener('shown.bs.offcanvas', updateToggles);
    sidebar.addEventListener('hidden.bs.offcanvas', updateToggles);

    sidebar.querySelector('.app-sidebar-nav')?.addEventListener('click', (event) => {
        if (! isCollapsedDesktop()) {
            return;
        }

        const branch = event.target.closest('.app-sidebar-nav > .nav > .app-sidebar-item > .app-sidebar-branch');

        if (! branch) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const item = branch.closest('.app-sidebar-item');
        const pin = ! item.classList.contains('app-sidebar-flyout-open');

        closeFlyouts();

        if (pin) {
            item.classList.add('app-sidebar-flyout-open');
        }
    });

    document.addEventListener('click', (event) => {
        if (! isCollapsedDesktop()) {
            return;
        }

        if (event.target.closest('.app-sidebar-nav > .nav > .app-sidebar-item')) {
            return;
        }

        closeFlyouts();
    });

    desktop.addEventListener('change', () => {
        if (desktop.matches) {
            window.bootstrap.Offcanvas.getInstance(sidebar)?.hide();
        }

        closeFlyouts();
        updateToggles();
    });

    updateToggles();
}
