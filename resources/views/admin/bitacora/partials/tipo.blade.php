@php
    $clases = match ($tipo) {
        'crear' => 'text-bg-success',
        'actualizar' => 'text-bg-primary',
        'eliminar' => 'text-bg-danger',
        'login' => 'text-bg-info',
        'logout' => 'text-bg-secondary',
        'login_fallido' => 'text-bg-warning',
        'bloqueo' => 'bg-danger-subtle text-danger-emphasis border border-danger-subtle',
        default => 'bg-body-secondary text-body-emphasis border',
    };
@endphp
<span class="badge {{ $clases }}">{{ \App\Models\Bitacora::etiquetaTipoAccion($tipo) }}</span>
