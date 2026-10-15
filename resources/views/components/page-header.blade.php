@props([
    'title',
    'icon' => null,
    'image' => null,
    'subtitle' => null,
    'section' => 'Seguridad',
    'parent' => null,
    'breadcrumb' => true,
])

@php
    $crumbs = [
        ['label' => 'Inicio', 'url' => route('home')],
    ];

    if ($section) {
        $crumbs[] = ['label' => $section];
    }

    if ($parent) {
        $crumbs[] = ['label' => $parent['label'], 'url' => $parent['url']];
    }

    $crumbs[] = ['label' => $title, 'active' => true];
@endphp

<header {{ $attributes->class(['view-header mb-4']) }}>
    <div class="view-header__top d-flex flex-wrap align-items-start justify-content-between gap-3">
        <div class="d-flex align-items-start gap-3 min-w-0">
            @if ($image)
                <img src="{{ $image }}" alt="" class="view-header__media" width="48" height="48">
            @elseif ($icon)
                <div class="view-header__icon" aria-hidden="true">
                    <i class="bi bi-{{ $icon }}"></i>
                </div>
            @endif

            <div class="min-w-0">
                <h1 class="view-header__title h3 mb-0">{{ $title }}</h1>
                @if ($subtitle)
                    <p class="view-header__subtitle text-body-secondary small mb-0 mt-1">{{ $subtitle }}</p>
                @endif
            </div>
        </div>

        @if ($slot->isNotEmpty())
            <div class="view-header__actions ms-auto">
                <x-page-actions>
                    {{ $slot }}
                </x-page-actions>
            </div>
        @endif
    </div>

    @if ($breadcrumb)
        <nav aria-label="breadcrumb" class="view-header__breadcrumb mt-2">
            <ol class="view-header__crumbs list-unstyled d-flex flex-wrap align-items-center justify-content-end gap-1 mb-0 small">
                @foreach ($crumbs as $crumb)
                    @if (! empty($crumb['active']))
                        <li class="view-header__crumb view-header__crumb--active" aria-current="page">{{ $crumb['label'] }}</li>
                    @elseif (! empty($crumb['url']))
                        <li class="view-header__crumb">
                            <a href="{{ $crumb['url'] }}">
                                @if ($crumb['label'] === 'Inicio')
                                    <i class="bi bi-house-door me-1" aria-hidden="true"></i>
                                @endif
                                {{ $crumb['label'] }}
                            </a>
                        </li>
                    @else
                        <li class="view-header__crumb view-header__crumb--muted">{{ $crumb['label'] }}</li>
                    @endif
                    @unless ($loop->last)
                        <li class="view-header__crumb-sep" aria-hidden="true"><i class="bi bi-chevron-right"></i></li>
                    @endunless
                @endforeach
            </ol>
        </nav>
    @endif
</header>
