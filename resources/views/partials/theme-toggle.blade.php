<li class="nav-item dropdown theme-toggle">
    <button type="button" class="btn btn-link nav-link dropdown-toggle d-flex align-items-center"
            id="theme-toggle" data-bs-toggle="dropdown" aria-expanded="false"
            data-theme-toggle-button aria-label="Cambiar tema">
        <i class="bi bi-circle-half" data-theme-toggle-icon></i>
        <span class="d-md-none ms-2">Tema</span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="theme-toggle">
        @foreach (['light' => ['sun-fill', 'Claro'], 'dark' => ['moon-stars-fill', 'Oscuro'], 'auto' => ['circle-half', 'Automático']] as $valor => [$icono, $etiqueta])
            <li>
                <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="{{ $valor }}" aria-pressed="false">
                    <i class="bi bi-{{ $icono }} me-2"></i> {{ $etiqueta }}
                    <i class="bi bi-check2 ms-auto d-none"></i>
                </button>
            </li>
        @endforeach
    </ul>
</li>
