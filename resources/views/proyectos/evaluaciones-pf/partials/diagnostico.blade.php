@php
    $v = $diagnostico['viabilidad'];
@endphp

<div @class([
    'card mb-4 diagnostico-pf-card border-start border-4',
    'border-success' => $v->color() === 'success',
    'border-warning' => $v->color() === 'warning',
    'border-danger' => $v->color() === 'danger',
    'border-secondary' => $v->color() === 'dark',
])>
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2 bg-body">
        <span class="fw-semibold">
            <i class="bi bi-clipboard2-pulse me-1" aria-hidden="true"></i>
            Diagnóstico de viabilidad e implementación
        </span>
        <x-viabilidad-pf-badge :viabilidad="$v" />
    </div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="small text-body-secondary">Puntaje de viabilidad</div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="fs-4 fw-bold">{{ $diagnostico['puntaje'] }}/100</span>
                    <x-viabilidad-pf-badge :viabilidad="$v" :short="true" class="d-md-none" />
                </div>
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
                    [$icono, $colorIcono] = match ($hallazgo['tipo']) {
                        'critico' => ['x-octagon-fill', 'text-danger'],
                        'alerta' => ['exclamation-triangle-fill', 'text-warning'],
                        default => ['info-circle-fill', 'text-primary'],
                    };
                @endphp
                <li class="d-flex gap-2 mb-2 small">
                    <i class="bi bi-{{ $icono }} {{ $colorIcono }} flex-shrink-0 mt-1" aria-hidden="true"></i>
                    <span>{{ $hallazgo['mensaje'] }}</span>
                </li>
            @endforeach
        </ul>
    </div>
</div>
