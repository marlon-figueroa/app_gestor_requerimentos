<nav class="app-topbar navbar navbar-expand navbar-dark bg-navy shadow-sm">
    <div class="container-fluid">
        <button type="button" class="btn border-0 app-sidebar-toggle px-2 me-2" data-sidebar-toggle
                aria-controls="app-sidebar" aria-label="Mostrar u ocultar menú lateral">
            <i data-feather="grid"></i>
        </button>

        <span class="navbar-text text-white fw-semibold text-truncate">
            @yield('title', 'Inicio')
        </span>

        <ul class="navbar-nav ms-auto align-items-center">
            <li class="nav-item dropdown me-2" id="notificaciones-menu">
                <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="notificaciones-toggle" title="Notificaciones">
                    <i class="bi bi-bell fs-5"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger d-none" id="notificaciones-badge">0</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end p-0" style="min-width: 22rem; max-width: 24rem;">
                    <div class="dropdown-header d-flex justify-content-between align-items-center">
                        <span>Notificaciones</span>
                        <button type="button" class="btn btn-link btn-sm p-0" id="notificaciones-marcar-todas">Marcar leídas</button>
                    </div>
                    <div id="notificaciones-lista" class="list-group list-group-flush small" style="max-height: 320px; overflow-y: auto;">
                        <div class="list-group-item text-body-secondary">Cargando…</div>
                    </div>
                </div>
            </li>
            @include('partials.theme-toggle')

            <li class="nav-item dropdown">
                <a id="user-menu" class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ Auth::user()->photoUrl() }}" alt="" class="rounded-circle object-fit-cover me-2" width="28" height="28">
                    <span class="d-none d-sm-inline">{{ Auth::user()->name }}</span>
                </a>

                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="user-menu">
                    <div class="dropdown-item-text">
                        <div class="fw-semibold">{{ Auth::user()->name }}</div>
                        <div class="small text-body-secondary">{{ Auth::user()->email }}</div>
                        <div class="small text-body-secondary">{{ Auth::user()->getRoleNames()->implode(', ') ?: 'Sin rol' }}</div>
                    </div>
                    <div class="dropdown-divider"></div>
                    @impersonating
                        <form method="POST" action="{{ route('impersonate.leave') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="bi bi-person-check me-2"></i> Volver a mi cuenta
                            </button>
                        </form>
                        <div class="dropdown-divider"></div>
                    @endImpersonating
                    <a class="dropdown-item" href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right me-2"></i> {{ __('Logout') }}
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </li>
        </ul>
    </div>
</nav>
