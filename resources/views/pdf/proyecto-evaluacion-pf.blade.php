<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $proyecto->codigo }} · {{ $evaluacion->nombre }}</title>
    <style>
        @page { margin: 90px 40px 60px 40px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #212529; }
        header { position: fixed; top: -70px; left: 0; right: 0; height: 60px; border-bottom: 2px solid #0d6efd; }
        header h1 { margin: 0; font-size: 16px; color: #0d6efd; }
        header .meta { font-size: 10px; color: #6c757d; }
        footer { position: fixed; bottom: -40px; left: 0; right: 0; height: 30px; font-size: 9px; color: #6c757d; text-align: right; }
        footer .page:after { content: counter(page); }
        h2 { font-size: 13px; color: #0d6efd; border-bottom: 1px solid #dee2e6; padding-bottom: 3px; margin: 16px 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        .datos td { padding: 4px 6px; border-bottom: 1px solid #dee2e6; vertical-align: top; }
        .datos td.etiqueta { width: 22%; font-weight: bold; background: #f8f9fa; }
        .lista th { background: #0d6efd; color: #fff; text-align: left; padding: 6px; font-size: 10px; }
        .lista td { padding: 5px 6px; border-bottom: 1px solid #dee2e6; vertical-align: top; }
        .lista tr:nth-child(even) td { background: #f8f9fa; }
        .totales { margin: 8px 0; }
        .totales span { display: inline-block; background: #e9ecef; padding: 4px 8px; border-radius: 3px; margin: 0 4px 4px 0; }
        .muted { color: #6c757d; }
    </style>
</head>
<body>
    <header>
        <h1>{{ config('app.name') }} &mdash; Puntos de función · {{ $proyecto->codigo }}</h1>
        <div class="meta">Generado el {{ now()->format('d/m/Y H:i') }} por {{ auth()->user()->name }}</div>
    </header>
    <footer>Página <span class="page"></span></footer>

    <h2 style="margin-top: 0">{{ $evaluacion->nombre }}</h2>

    <table class="datos">
        <tr>
            <td class="etiqueta">Proyecto</td>
            <td>{{ $proyecto->codigo }} · {{ $proyecto->nombre }}</td>
            <td class="etiqueta">Responsable</td>
            <td>{{ $proyecto->responsable?->name }}</td>
        </tr>
        <tr>
            <td class="etiqueta">Autor evaluación</td>
            <td>{{ $evaluacion->creador?->name ?? '—' }}</td>
            <td class="etiqueta">Fecha</td>
            <td>{{ $evaluacion->updated_at?->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    @if ($evaluacion->notas)
        <h2>Notas</h2>
        <p>{!! nl2br(e($evaluacion->notas)) !!}</p>
    @endif

    <h2>Totales IFPUG</h2>
    <div class="totales">
        <span><strong>PF sin ajuste:</strong> {{ $evaluacion->total_sin_ajuste }}</span>
        <span><strong>Suma influencia:</strong> {{ $evaluacion->suma_influencia }}</span>
        <span><strong>VAF:</strong> {{ number_format($evaluacion->factor_ajuste, 2, '.', '') }}</span>
        <span><strong>PF ajustados:</strong> {{ number_format($evaluacion->total_ajustado, 2, '.', '') }}</span>
    </div>

    <h2>Diagnóstico de viabilidad e implementación</h2>
    <table class="datos">
        <tr>
            <td class="etiqueta">Viabilidad</td>
            <td>{{ $diagnostico['viabilidad']->label() }} ({{ $diagnostico['puntaje'] }}/100)</td>
            <td class="etiqueta">Esfuerzo</td>
            <td>{{ number_format($diagnostico['tiempo']['horas'], 1, '.', '') }} h · {{ $diagnostico['tiempo']['dias_laborables'] }} días lab.</td>
        </tr>
        <tr>
            <td class="etiqueta">Fin estimado</td>
            <td>{{ $diagnostico['tiempo']['fecha_fin_estimada'] ?? '—' }}</td>
            <td class="etiqueta">Plazo proyecto</td>
            <td>
                @if ($diagnostico['plazo']['dias_disponibles'] === null)
                    Sin fecha de finalización
                @elseif ($diagnostico['plazo']['cumple_plazo'])
                    Cumple (holgura {{ $diagnostico['plazo']['holgura_dias'] }} días lab.)
                @else
                    Insuficiente
                @endif
            </td>
        </tr>
    </table>
    <ul style="font-size: 10px; padding-left: 16px;">
        @foreach ($diagnostico['hallazgos'] as $hallazgo)
            <li>[{{ strtoupper($hallazgo['tipo']) }}] {{ $hallazgo['mensaje'] }}</li>
        @endforeach
    </ul>

    <h2>Componentes funcionales</h2>
    <table class="lista">
        <thead>
            <tr>
                <th style="width: 8%">Tipo</th>
                <th style="width: 40%">Nombre</th>
                <th style="width: 8%">DET</th>
                <th style="width: 8%">FTR/RET</th>
                <th style="width: 12%">Complejidad</th>
                <th style="width: 8%">PF</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($evaluacion->componentes as $componente)
                <tr>
                    <td>{{ $componente->tipo->abreviatura() }}</td>
                    <td>{{ $componente->nombre }}</td>
                    <td>{{ $componente->det }}</td>
                    <td>{{ $componente->referencias }}</td>
                    <td>{{ $componente->complejidad->label() }}</td>
                    <td>{{ $componente->puntos }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Desglose por tipo</h2>
    <table class="lista">
        <thead>
            <tr>
                <th>Tipo</th>
                <th style="width: 15%">Cantidad</th>
                <th style="width: 15%">PF</th>
            </tr>
        </thead>
        <tbody>
            @foreach (\App\Enums\TipoComponentePf::cases() as $tipo)
                @php $fila = $resumen[$tipo->value]; @endphp
                @if ($fila['conteo'] > 0)
                    <tr>
                        <td>{{ $tipo->label() }}</td>
                        <td>{{ $fila['conteo'] }}</td>
                        <td>{{ $fila['puntos'] }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <h2>Características generales (0–5)</h2>
    <table class="lista">
        <thead>
            <tr>
                <th>Característica</th>
                <th style="width: 12%">Grado</th>
            </tr>
        </thead>
        <tbody>
            @foreach (\App\Enums\CaracteristicaGeneralPf::ordenadas() as $caracteristica)
                <tr>
                    <td>{{ $caracteristica->label() }}</td>
                    <td>{{ $evaluacion->caracteristicas[$caracteristica->value] ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
