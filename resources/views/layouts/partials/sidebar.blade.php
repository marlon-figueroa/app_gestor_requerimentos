<aside class="app-sidebar">
    <div class="offcanvas-lg offcanvas-start app-sidebar-panel" tabindex="-1" id="app-sidebar" aria-labelledby="app-sidebar-label" data-bs-theme="dark">
        <div class="app-sidebar-brand">
            <a href="{{ route('home') }}" class="d-flex align-items-center text-decoration-none text-white" id="app-sidebar-label">
                <i data-feather="clipboard" class="fs-4 me-2"></i>
                <span class="fw-bold text-truncate">{{ config('app.name', 'Laravel') }}</span>
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
