@if ($proyecto->vencido)
    <span class="text-danger fw-semibold" title="Fecha de finalización vencida">
        <i class="bi bi-exclamation-triangle me-1"></i>{{ $proyecto->fecha_finalizacion->format('d/m/Y') }}
    </span>
@else
    {{ $proyecto->fecha_finalizacion?->format('d/m/Y') }}
@endif
