@php
    $usuarioEtiqueta = $sesion->user
        ? "{$sesion->user->name} ({$sesion->user->email})"
        : '—';
    $inicioEstimado = $eventos->first()?->created_at;
    $iconoTipo = fn (string $codigo) => match ($codigo) {
        'crear' => 'plus-circle',
        'actualizar' => 'pencil-square',
        'eliminar' => 'trash',
        'login' => 'box-arrow-in-right',
        'logout' => 'box-arrow-right',
        'login_fallido' => 'shield-x',
        'bloqueo' => 'lock',
        default => 'gear',
    };
@endphp

<dl class="row small mb-4">
    <dt class="col-sm-3">Usuario</dt>
    <dd class="col-sm-9">{{ $usuarioEtiqueta }}</dd>
    <dt class="col-sm-3">IP</dt>
    <dd class="col-sm-9">{{ $sesion->ip_address ?: '—' }}</dd>
    <dt class="col-sm-3">Cliente</dt>
    <dd class="col-sm-9">{{ $sesion->resumenCliente() ?: '—' }}</dd>
    <dt class="col-sm-3">Inicio (estimado)</dt>
    <dd class="col-sm-9">{{ $inicioEstimado?->format('d/m/Y H:i:s') ?? '—' }}</dd>
    <dt class="col-sm-3">Última actividad</dt>
    <dd class="col-sm-9">{{ $sesion->last_activity_at?->format('d/m/Y H:i:s') ?? '—' }}</dd>
</dl>

@if ($eventos->isEmpty())
    <p class="text-muted mb-0">No hay eventos de bitácora asociados a esta sesión.</p>
@else
    <ol class="sesion-timeline mb-0">
        @foreach ($eventos as $evento)
            <li class="sesion-timeline__item @if (! $evento->exitoso) sesion-timeline__item--error @endif">
                <span class="sesion-timeline__marker" aria-hidden="true"></span>
                <div class="sesion-timeline__time">{{ $evento->created_at?->format('d/m/Y H:i:s') }}</div>
                <div class="sesion-timeline__title">
                    <i class="bi bi-{{ $iconoTipo($evento->tipo_accion) }} me-1"></i>
                    {{ \App\Models\Bitacora::etiquetaTipoAccion($evento->tipo_accion) }} — {{ $evento->accion }}
                </div>
                @if ($evento->descripcion)
                    <div>{{ $evento->descripcion }}</div>
                @endif
                <div class="sesion-timeline__meta">
                    @if ($evento->modulo)
                        <span class="me-2">{{ $evento->modulo }}</span>
                    @endif
                    @if ($evento->ruta)
                        <code>/{{ $evento->ruta }}</code>
                    @endif
                    · {{ $evento->exitoso ? 'Exitoso' : 'Fallido' }}
                    · <a href="{{ route('admin.bitacora.show', $evento) }}" class="link-primary">Ver registro</a>
                </div>
            </li>
        @endforeach
    </ol>
@endif
