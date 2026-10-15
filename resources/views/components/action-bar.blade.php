@props([
    'create' => null,
    'createLabel' => 'Nuevo',
    'createIcon' => 'plus-lg',
    'createPermission' => null,
    'export' => null,
    'exportLabel' => 'Exportar PDF',
    'exportPermission' => null,
    'table' => null,
    'groupLabel' => null,
])

@php
    $puede = fn (?string $permiso) => $permiso === null || (bool) auth()->user()?->can($permiso);
    $tieneExport = $export && $puede($exportPermission);
    $tieneCreate = $create && $puede($createPermission);
@endphp

@if ($tieneExport || $tieneCreate || ! $slot->isEmpty())
    <x-page-action-group :label="$groupLabel">
        {{ $slot }}

        @if ($tieneExport)
            @if ($table)
                <x-action-btn variant="export" icon="file-earmark-pdf" :label="$exportLabel" :href="$export" data-export-table="{{ $table }}" />
            @else
                <x-action-btn variant="export" icon="file-earmark-pdf" :label="$exportLabel" :href="$export" />
            @endif
        @endif

        @if ($tieneCreate)
            <x-action-btn variant="create" :icon="$createIcon" :label="$createLabel" :href="$create" />
        @endif
    </x-page-action-group>
@endif
