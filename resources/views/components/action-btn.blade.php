@props([
    'href' => null,
    'variant' => 'nav',
    'icon',
    'label' => null,
    'type' => 'button',
])

@php
    $claseVariante = match ($variant) {
        'create' => 'btn-primary',
        'edit' => 'btn-outline-primary',
        'export' => 'btn-outline-secondary',
        'nav' => 'btn-outline-secondary',
        'tool' => 'btn-outline-secondary',
        'danger' => 'btn-outline-danger',
        'success' => 'btn-success',
        'success-outline' => 'btn-outline-success',
        'warning' => 'btn-warning',
        'cancel' => 'btn-outline-secondary',
        default => 'btn-outline-secondary',
    };

@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class(['btn', $claseVariante, 'btn-action']) }}>
        <i class="bi bi-{{ $icon }}" aria-hidden="true"></i>
        @if ($label)
            <span>{{ $label }}</span>
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class(['btn', $claseVariante, 'btn-action']) }}>
        <i class="bi bi-{{ $icon }}" aria-hidden="true"></i>
        @if ($label)
            <span>{{ $label }}</span>
        @endif
    </button>
@endif
