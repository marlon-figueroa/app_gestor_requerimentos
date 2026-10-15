@php
    $suplantarId = null;

    if (
        isset($impersonateUser)
        && auth()->user()?->canImpersonate()
        && auth()->user()->isNot($impersonateUser)
        && $impersonateUser->canBeImpersonated()
    ) {
        $suplantarId = 'impersonate-'.$impersonateUser->getKey();
    }
@endphp

{{-- El formulario queda fuera del grupo: Bootstrap solo une los .btn que son hijos directos. --}}
@if ($suplantarId)
    <form id="{{ $suplantarId }}" method="POST" action="{{ route('impersonate', $impersonateUser) }}" hidden>
        @csrf
    </form>
@endif

<div class="btn-group btn-group-sm" role="group">
    @if ($suplantarId)
        <button type="submit" form="{{ $suplantarId }}" class="btn btn-outline-secondary" title="Ingresar como este usuario">
            <i class="bi bi-person-bounding-box"></i>
        </button>
    @endif
    @can($editPermission)
        <a href="{{ $editUrl }}" class="btn btn-outline-primary" title="Editar">
            <i class="bi bi-pencil-square"></i>
        </a>
    @endcan
    @if ($deleteUrl)
        @can($deletePermission)
            <button type="button" class="btn btn-outline-danger" title="Eliminar"
                    data-delete-url="{{ $deleteUrl }}" data-confirm="{{ $confirm }}">
                <i class="bi bi-trash"></i>
            </button>
        @endcan
    @endif
</div>
