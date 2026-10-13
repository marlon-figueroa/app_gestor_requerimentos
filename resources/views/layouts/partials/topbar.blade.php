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
            @include('partials.theme-toggle')

            <li class="nav-item dropdown">
                <a id="user-menu" class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ asset('image/user_image.png') }}" alt="" class="rounded-circle me-2" width="28" height="28">
                    <span class="d-none d-sm-inline">{{ Auth::user()->name }}</span>
                </a>

                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="user-menu">
                    <div class="dropdown-item-text">
                        <div class="fw-semibold">{{ Auth::user()->name }}</div>
                        <div class="small text-body-secondary">{{ Auth::user()->email }}</div>
                        <div class="small text-body-secondary">{{ Auth::user()->getRoleNames()->implode(', ') ?: 'Sin rol' }}</div>
                    </div>
                    <div class="dropdown-divider"></div>
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
