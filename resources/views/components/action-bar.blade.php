@props([
    'create' => null,
    'createLabel' => 'Nuevo',
    'createIcon' => 'plus-lg',
    'createPermission' => null,
    'export' => null,
    'exportLabel' => 'Exportar PDF',
    'exportPermission' => null,
    'table' => null,
])

@php
    $puede = fn (?string $permiso) => $permiso === null || (bool) auth()->user()?->can($permiso);
@endphp

<div {{ $attributes->class(['action-bar d-flex flex-wrap align-items-center gap-2']) }}>
    {{ $slot }}

    @if ($export && $puede($exportPermission))
        <a href="{{ $export }}" class="btn btn-outline-danger" @if ($table) data-export-table="{{ $table }}" @endif>
            <i class="bi bi-file-earmark-pdf"></i> {{ $exportLabel }}
        </a>
    @endif

    @if ($create && $puede($createPermission))
        <a href="{{ $create }}" class="btn btn-primary">
            <i class="bi bi-{{ $createIcon }}"></i> {{ $createLabel }}
        </a>
    @endif
</div>
