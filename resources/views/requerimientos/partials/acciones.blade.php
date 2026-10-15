<div class="btn-group btn-group-sm" role="group">
    <a href="{{ route('requerimientos.show', $requerimiento) }}" class="btn btn-outline-secondary" title="Ver">
        <i class="bi bi-eye"></i>
    </a>
    @can('update', $requerimiento)
        <a href="{{ route('requerimientos.edit', $requerimiento) }}" class="btn btn-outline-primary" title="Editar">
            <i class="bi bi-pencil-square"></i>
        </a>
    @endcan
    @can('export', $requerimiento)
        <a href="{{ route('requerimientos.pdf', $requerimiento) }}" class="btn btn-outline-danger" title="Exportar PDF" target="_blank">
            <i class="bi bi-file-earmark-pdf"></i>
        </a>
    @endcan
    @can('delete', $requerimiento)
        <x-delete-button :url="route('requerimientos.destroy', $requerimiento)"
                         :confirm="'¿Eliminar el requerimiento '.$requerimiento->codigo.'?'" />
    @endcan
</div>
