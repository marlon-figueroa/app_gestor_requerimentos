<div class="btn-group btn-group-sm" role="group">
    <a href="{{ route('proyectos.show', $proyecto) }}" class="btn btn-outline-secondary" title="Ver">
        <i class="bi bi-eye"></i>
    </a>
    @can('update', $proyecto)
        <a href="{{ route('proyectos.edit', $proyecto) }}" class="btn btn-outline-primary" title="Editar">
            <i class="bi bi-pencil-square"></i>
        </a>
    @endcan
    @can('export', $proyecto)
        <a href="{{ route('proyectos.pdf', $proyecto) }}" class="btn btn-outline-danger" title="Reporte de requerimientos" target="_blank">
            <i class="bi bi-file-earmark-pdf"></i>
        </a>
    @endcan
</div>
