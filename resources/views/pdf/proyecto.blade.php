<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $proyecto->codigo }} · Requerimientos del proyecto</title>
    <style>
        @page { margin: 90px 40px 60px 40px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #212529; }
        header { position: fixed; top: -70px; left: 0; right: 0; height: 60px; border-bottom: 2px solid {{ config('branding.primary') }}; }
        header h1 { margin: 0; font-size: 18px; color: {{ config('branding.primary') }}; }
        header .meta { font-size: 10px; color: #6c757d; }
        footer { position: fixed; bottom: -40px; left: 0; right: 0; height: 30px; font-size: 9px; color: #6c757d; text-align: right; }
        footer .page:after { content: counter(page); }
        h2 { font-size: 13px; color: {{ config('branding.primary') }}; border-bottom: 1px solid #dee2e6; padding-bottom: 3px; margin: 16px 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        .datos td { padding: 4px 6px; border-bottom: 1px solid #dee2e6; vertical-align: top; }
        .datos td.etiqueta { width: 16%; font-weight: bold; background: #f8f9fa; }
        .estado { display: inline-block; padding: 2px 6px; border-radius: 3px; color: #fff; font-size: 10px; }
        .totales span { display: inline-block; background: #e9ecef; padding: 3px 8px; border-radius: 3px; margin: 0 4px 4px 0; font-size: 10px; }
        .lista th { background: {{ config('branding.primary') }}; color: #fff; text-align: left; padding: 6px; }
        .lista td { padding: 5px 6px; border-bottom: 1px solid #dee2e6; vertical-align: top; }
        .lista tr:nth-child(even) td { background: #f8f9fa; }
        .vencido { color: #dc3545; font-weight: bold; }
        .muted { color: #6c757d; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <header>
        <h1>{{ config('app.name') }} &mdash; Requerimientos del proyecto {{ $proyecto->codigo }}</h1>
        <div class="meta">Generado el {{ now()->format('d/m/Y H:i') }} por {{ auth()->user()->name }}</div>
    </header>

    <footer>
        Página <span class="page"></span>
    </footer>

    <table class="datos">
        <tr>
            <td class="etiqueta">Proyecto</td>
            <td>{{ $proyecto->codigo }} &middot; {{ $proyecto->nombre }}</td>
            <td class="etiqueta">Estado</td>
            <td><span class="estado" style="background: {{ $proyecto->estado->hex() }}">{{ $proyecto->estado->label() }}</span></td>
        </tr>
        <tr>
            <td class="etiqueta">Responsable</td>
            <td>{{ $proyecto->responsable?->name }}</td>
            <td class="etiqueta">Finalización</td>
            <td @class(['vencido' => $proyecto->vencido])>
                {{ $proyecto->fecha_finalizacion?->format('d/m/Y') }}
                @if ($proyecto->vencido) (vencida) @endif
            </td>
        </tr>
    </table>

    <h2>Resumen</h2>
    <div class="totales">
        <span><strong>Total:</strong> {{ $proyecto->requerimientos->count() }}</span>
        @foreach ($totales as $fila)
            <span>{{ $fila['estado']->label() }}: {{ $fila['total'] }}</span>
        @endforeach
    </div>

    <h2>Requerimientos asociados</h2>
    <table class="lista">
        <thead>
            <tr>
                <th style="width: 9%">Código</th>
                <th style="width: 31%">Título</th>
                <th style="width: 16%">Solicitante</th>
                <th style="width: 16%">Gestor</th>
                <th style="width: 10%">Estado</th>
                <th style="width: 9%">Solicitud</th>
                <th style="width: 9%">Vencimiento</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($proyecto->requerimientos as $requerimiento)
                <tr>
                    <td>{{ $requerimiento->codigo }}</td>
                    <td>{{ $requerimiento->titulo }}</td>
                    <td>{{ $requerimiento->solicitante?->name }}</td>
                    <td>{{ $requerimiento->gestor?->name ?? 'Sin asignar' }}</td>
                    <td><span class="estado" style="background: {{ $requerimiento->estado->hex() }}">{{ $requerimiento->estado->label() }}</span></td>
                    <td>{{ $requerimiento->fecha_solicitud?->format('d/m/Y') }}</td>
                    <td @class(['vencido' => $requerimiento->vencido])>{{ $requerimiento->fecha_vencimiento?->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center muted">El proyecto aún no tiene requerimientos.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
