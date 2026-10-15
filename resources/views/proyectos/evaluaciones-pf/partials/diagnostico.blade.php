@php
    $v = $diagnostico['viabilidad'];
@endphp

<div class="card mb-4 border-{{ $v->color() }}">
    <div class="card-header d-flex align-items-center justify-content-between bg-{{ $v->color() }} bg-opacity-10">
        <span><i class="bi bi-clipboard2-pulse me-1"></i> Diagnóstico de viabilidad e implementación</span>
        <span class="badge text-bg-{{ $v->color() }}">
            <i class="bi bi-{{ $v->icono() }} me-1"></i>{{ $v->label() }}
        </span>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="small text-body-secondary">Puntaje de viabilidad</div>
                <div class="fs-4 fw-bold">{{ $diagnostico['puntaje'] }}/100</div>
            </div>
            <div class="col-md-4">
                <div class="small text-body-secondary">Esfuerzo estimado</div>
                <div class="fw-semibold">{{ number_format($diagnostico['tiempo']['horas'], 1, ',', '.') }} h</div>
                <div class="small">{{ $diagnostico['tiempo']['dias_laborables'] }} días lab. · {{ number_format($diagnostico['tiempo']['semanas'], 1, ',', '.') }} sem.</div>
            </div>
            <div class="col-md-4">
                <div class="small text-body-secondary">Fin estimado (1 recurso)</div>
                <div class="fw-semibold">{{ $diagnostico['tiempo']['fecha_fin_estimada'] ?? '—' }}</div>
                @if ($diagnostico['plazo']['dias_disponibles'] !== null)
                    <div class="small">
                        Plazo proyecto:
                        @if ($diagnostico['plazo']['cumple_plazo'])
                            <span class="text-success">cumple</span>
                            (holgura {{ $diagnostico['plazo']['holgura_dias'] }} días lab.)
                        @else
                            <span class="text-danger">insuficiente</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <h3 class="h6 text-primary">Hallazgos</h3>
        <ul class="list-unstyled mb-0">
            @foreach ($diagnostico['hallazgos'] as $hallazgo)
                @php
                    $icono = match ($hallazgo['tipo']) {
                        'critico' => 'x-octagon-fill text-danger',
                        'alerta' => 'exclamation-triangle-fill text-warning',
                        default => 'info-circle text-primary',
                    };
                @endphp
                <li class="d-flex gap-2 mb-2 small">
                    <i class="bi bi-{{ $icono }} flex-shrink-0 mt-1"></i>
                    <span>{{ $hallazgo['mensaje'] }}</span>
                </li>
            @endforeach
        </ul>
    </div>
</div>
