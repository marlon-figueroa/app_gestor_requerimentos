<aside class="app-sidebar">
    <div class="offcanvas-lg offcanvas-start app-sidebar-panel" tabindex="-1" id="app-sidebar" aria-labelledby="app-sidebar-label">
        <div class="app-sidebar-brand">
            <a href="{{ route('home') }}" class="d-flex align-items-center gap-2 text-decoration-none" id="app-sidebar-label">
                <span class="app-sidebar-brand-mark" aria-hidden="true">
                    <i data-feather="clipboard"></i>
                </span>
                <span class="text-truncate">{{ config('app.name', 'Laravel') }}</span>
            </a>
            <button type="button" class="btn-close d-lg-none ms-auto" data-bs-dismiss="offcanvas" data-bs-target="#app-sidebar" aria-label="Cerrar menú"></button>
        </div>

        <nav class="app-sidebar-nav" aria-label="Menú principal">
            <ul class="nav flex-column">
                @each('layouts.partials.sidebar-item', $menu, 'item')
            </ul>
        </nav>
    </div>
</aside>
