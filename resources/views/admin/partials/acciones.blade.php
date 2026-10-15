<div class="btn-group btn-group-sm" role="group">
    @isset($impersonateUser)
        @canImpersonate
            @canBeImpersonated($impersonateUser)
                <form method="POST" action="{{ route('impersonate', $impersonateUser) }}" class="d-contents">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary" title="Ingresar como este usuario">
                        <i class="bi bi-person-bounding-box"></i>
                    </button>
                </form>
            @endCanBeImpersonated
        @endCanImpersonate
    @endisset
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
