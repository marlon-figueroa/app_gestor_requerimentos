@props([
    'icon',
    'titulo',
    'texto',
])

<div class="perfil-vacio text-center py-4 px-3">
    <div class="perfil-vacio__icon rounded-circle mx-auto mb-3">
        <i class="bi bi-{{ $icon }}"></i>
    </div>
    <p class="fw-semibold mb-1">{{ $titulo }}</p>
    <p class="text-body-secondary small mb-0">{{ $texto }}</p>
</div>
