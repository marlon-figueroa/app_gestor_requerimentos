@props([
    'url',
    'confirm' => null,
    'permission' => null,
    'label' => null,
    'redirect' => null,
    'size' => null,
])

@if ($url && ($permission === null || auth()->user()?->can($permission)))
    <button type="button" title="Eliminar"
            {{ $attributes->class(['btn btn-outline-danger', 'btn-'.$size => $size]) }}
            data-delete-url="{{ $url }}"
            @if ($confirm) data-confirm="{{ $confirm }}" @endif
            @if ($redirect) data-redirect="{{ $redirect }}" @endif>
        <i class="bi bi-trash"></i>
        @if ($label)
            {{ $label }}
        @endif
    </button>
@endif
