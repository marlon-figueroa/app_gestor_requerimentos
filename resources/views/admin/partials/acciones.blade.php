<div class="btn-group btn-group-sm" role="group">
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
