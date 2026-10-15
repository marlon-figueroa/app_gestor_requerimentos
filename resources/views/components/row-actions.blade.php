@props([
    'editUrl' => null,
    'editPermission' => null,
    'deleteUrl' => null,
    'deletePermission' => null,
    'confirm' => null,
])

@php
    $puedeEditar = $editUrl && ($editPermission === null || auth()->user()?->can($editPermission));
@endphp

<div class="btn-group btn-group-sm" role="group">
    {{ $slot ?? '' }}

    @if ($puedeEditar)
        <a href="{{ $editUrl }}" class="btn btn-outline-primary" title="Editar">
            <i class="bi bi-pencil-square"></i>
        </a>
    @endif

    <x-delete-button :url="$deleteUrl" :permission="$deletePermission" :confirm="$confirm" />
</div>
