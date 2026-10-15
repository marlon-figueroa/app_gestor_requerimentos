@php
    $acciones = collect(\App\Services\RequerimientoFlujoService::accionesPosibles($requerimiento))
        ->filter(fn (string $accion) => auth()->user()->can($accion, $requerimiento));
    $destinos = $acciones->reject(fn (string $accion) => $accion === 'tomar')
        ->map(fn (string $accion) => \App\Services\RequerimientoFlujoService::ACCIONES[$accion]->value)
        ->values();
@endphp

<div @class(['kanban__tarjeta', 'kanban__tarjeta--vencida' => $requerimiento->vencido])
     data-id="{{ $requerimiento->id }}"
     data-codigo="{{ $requerimiento->codigo }}"
     data-estado="{{ $requerimiento->estado->value }}"
     data-destinos="{{ $destinos->implode(',') }}"
     data-arrastrable="{{ $destinos->isNotEmpty() ? '1' : '0' }}">
    <div class="d-flex align-items-center justify-content-between gap-2">
        <span class="badge text-bg-light border">{{ $requerimiento->codigo }}</span>
        @if ($requerimiento->vencido)
            <span class="badge text-bg-danger" title="Plazo vencido"><i class="bi bi-alarm"></i> Vencido</span>
        @endif
    </div>

    <a href="{{ route('requerimientos.show', $requerimiento) }}" class="kanban__titulo">{{ $requerimiento->titulo }}</a>

    <div class="kanban__meta">
        <div title="Solicitante"><i class="bi bi-person me-1"></i>{{ $requerimiento->solicitante?->name }}</div>
        <div title="Gestor">
            <i class="bi bi-person-check me-1"></i>
            {{ $requerimiento->gestor?->name ?? ($requerimiento->enBandeja() ? 'En bandeja' : 'Sin gestor') }}
        </div>
        <div class="d-flex justify-content-between mt-1">
            <span title="Vencimiento"><i class="bi bi-calendar-event me-1"></i>{{ $requerimiento->fecha_vencimiento->format('d/m/Y') }}</span>
            <span>
                <span class="me-2" title="Casos de uso"><i class="bi bi-diagram-3"></i> {{ $requerimiento->casos_uso_count }}</span>
                <span title="Adjuntos"><i class="bi bi-paperclip"></i> {{ $requerimiento->adjuntos_count }}</span>
            </span>
        </div>
    </div>

    @if ($acciones->contains('tomar'))
        <form method="POST" action="{{ route('requerimientos.flujo', [$requerimiento, 'tomar']) }}" class="mt-2"
              data-confirm="Quedará asignado como gestor de {{ $requerimiento->codigo }}." data-confirm-title="¿Tomar para revisión?">
            @csrf
            <button type="submit" class="btn btn-sm btn-warning w-100">
                <i class="bi bi-hand-index"></i> Tomar para revisión
            </button>
        </form>
    @endif
</div>
