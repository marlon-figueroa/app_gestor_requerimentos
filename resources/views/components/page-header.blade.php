@props(['title', 'icon', 'parent' => null])

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="bi bi-{{ $icon }} me-2"></i>{{ $title }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                <li class="breadcrumb-item">Seguridad</li>
                @if ($parent)
                    <li class="breadcrumb-item"><a href="{{ $parent['url'] }}">{{ $parent['label'] }}</a></li>
                @endif
                <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        {{ $slot }}
    </div>
</div>
