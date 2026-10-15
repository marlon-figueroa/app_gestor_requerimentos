@if ($requerimiento->vencido)
    <span class="text-danger fw-semibold" title="Plazo vencido">
        <i class="bi bi-exclamation-triangle me-1"></i>{{ $requerimiento->fecha_vencimiento->format('d/m/Y') }}
    </span>
@else
    {{ $requerimiento->fecha_vencimiento?->format('d/m/Y') }}
@endif
