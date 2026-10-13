<div class="btn-group btn-group-sm" role="group">
    <a href="{{ route('admin.bitacora.sesiones.show', $sesion->id) }}" class="btn btn-outline-primary" title="Ver timeline de la sesión">
        <i class="bi bi-clock-history"></i> Timeline
    </a>
    @can('bitacora.invalidar_sesion')
        @if (session()->getId() !== $sesion->id)
            <button type="button"
                    class="btn btn-outline-danger"
                    title="Invalidar sesión y desconectar al usuario"
                    data-invalidate-session-url="{{ route('admin.bitacora.sesiones.invalidar', $sesion->id) }}"
                    data-usuario="{{ $sesion->user?->name ?? 'Usuario' }}">
                <i class="bi bi-box-arrow-right"></i> Invalidar
            </button>
        @endif
    @endcan
</div>
