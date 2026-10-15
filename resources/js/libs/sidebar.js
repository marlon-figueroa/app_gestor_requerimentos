/**
 * Boton de menu del topbar: en escritorio (>= lg) estrecha el sidebar (solo iconos) o lo
 * expande, y recuerda la preferencia; en pantallas pequenas abre el sidebar como offcanvas.
 * Con el sidebar colapsado, un clic en una rama de primer nivel expande el panel completo.
 */
const STORAGE_KEY = 'sidebar';
const desktop = window.matchMedia('(min-width: 992px)');

const topLevelBranchSelector = '.app-sidebar-nav > .nav > .app-sidebar-item > .app-sidebar-branch';

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

function expandSidebarDesktop() {
    document.documentElement.classList.remove('sidebar-collapsed');
    storeCollapsed(false);
    updateToggles();
}

function openSidebarBranch(branchButton) {
    const selector = branchButton.getAttribute('data-bs-target');

    if (! selector) {
        return;
    }

    const panel = document.querySelector(selector);

    if (! panel || ! window.bootstrap?.Collapse) {
        return;
    }

    window.bootstrap.Collapse.getOrCreateInstance(panel, { toggle: false }).show();
    branchButton.classList.remove('collapsed');
    branchButton.setAttribute('aria-expanded', 'true');
}

function updateToggles() {
    const expanded = desktop.matches
        ? ! document.documentElement.classList.contains('sidebar-collapsed')
        : document.getElementById('app-sidebar')?.classList.contains('show') ?? false;

    document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
        button.setAttribute('aria-expanded', String(expanded));
    });
}

export function initSidebar() {
    const sidebar = document.getElementById('app-sidebar');

    if (! sidebar) {
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

    sidebar.querySelector('.app-sidebar-nav')?.addEventListener('click', (event) => {
        if (! isCollapsedDesktop()) {
            return;
        }

        const branch = event.target.closest(topLevelBranchSelector);

        if (! branch) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        expandSidebarDesktop();
        openSidebarBranch(branch);
    });

    desktop.addEventListener('change', () => {
        if (desktop.matches) {
            window.bootstrap.Offcanvas.getInstance(sidebar)?.hide();
        }

        updateToggles();
    });

    updateToggles();
}
