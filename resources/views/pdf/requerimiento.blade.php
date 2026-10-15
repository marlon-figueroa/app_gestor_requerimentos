<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $requerimiento->codigo }} · {{ $requerimiento->titulo }}</title>
    <style>
        @page { margin: 90px 40px 60px 40px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #212529; }
        header { position: fixed; top: -70px; left: 0; right: 0; height: 60px; border-bottom: 2px solid #0d6efd; }
        header h1 { margin: 0; font-size: 16px; color: #0d6efd; }
        header .meta { font-size: 10px; color: #6c757d; }
        footer { position: fixed; bottom: -40px; left: 0; right: 0; height: 30px; font-size: 9px; color: #6c757d; text-align: right; }
        footer .page:after { content: counter(page); }
        h2 { font-size: 13px; color: #0d6efd; border-bottom: 1px solid #dee2e6; padding-bottom: 3px; margin: 18px 0 8px; }
        h3 { font-size: 12px; margin: 0 0 4px; }
        table { width: 100%; border-collapse: collapse; }
        .datos td { padding: 4px 6px; border-bottom: 1px solid #dee2e6; vertical-align: top; }
        .datos td.etiqueta { width: 22%; font-weight: bold; background: #f8f9fa; }
        .estado { display: inline-block; padding: 2px 6px; border-radius: 3px; color: #fff; font-size: 10px; }
        .vencido { color: #dc3545; font-weight: bold; }
        .texto { line-height: 1.45; }
        .caso { border: 1px solid #dee2e6; border-radius: 4px; padding: 8px; margin-bottom: 8px; page-break-inside: avoid; }
        .caso .sub { font-size: 10px; color: #6c757d; margin-bottom: 4px; }
        .caso .campo { margin-top: 5px; }
        .caso .campo strong { display: block; font-size: 10px; color: #495057; }
        .adjunto-imagen { margin-bottom: 12px; page-break-inside: avoid; text-align: center; }
        .adjunto-imagen img { max-width: 100%; max-height: 420px; border: 1px solid #dee2e6; }
        .adjunto-imagen .pie { font-size: 10px; color: #6c757d; margin-top: 3px; }
        .lista-pdf td, .historial td, .historial th { padding: 5px 6px; border-bottom: 1px solid #dee2e6; vertical-align: top; }
        .historial th { background: #0d6efd; color: #fff; text-align: left; }
        a { color: #0d6efd; }
        .nota { font-size: 9px; color: #6c757d; margin-top: 4px; }
        .muted { color: #6c757d; }
    </style>
</head>
<body>
    <header>
        <h1>{{ config('app.name') }} &mdash; {{ $requerimiento->codigo }}</h1>
        <div class="meta">Generado el {{ now()->format('d/m/Y H:i') }} por {{ auth()->user()->name }}</div>
    </header>

    <footer>
        Página <span class="page"></span>
    </footer>

    <h2 style="margin-top: 0">{{ $requerimiento->titulo }}</h2>

    <table class="datos">
        <tr>
            <td class="etiqueta">Proyecto</td>
            <td colspan="3">{{ $requerimiento->proyecto ? $requerimiento->proyecto->codigo.' · '.$requerimiento->proyecto->nombre : 'Sin proyecto' }}</td>
        </tr>
        <tr>
            <td class="etiqueta">Estado</td>
            <td><span class="estado" style="background: {{ $requerimiento->estado->hex() }}">{{ $requerimiento->estado->label() }}</span></td>
            <td class="etiqueta">Solicitante</td>
            <td>{{ $requerimiento->solicitante?->name }}</td>
        </tr>
        <tr>
            <td class="etiqueta">Fecha de solicitud</td>
            <td>{{ $requerimiento->fecha_solicitud?->format('d/m/Y') }}</td>
            <td class="etiqueta">Gestor</td>
            <td>{{ $requerimiento->gestor?->name ?? 'Sin asignar' }}</td>
        </tr>
        <tr>
            <td class="etiqueta">Vencimiento</td>
            <td @class(['vencido' => $requerimiento->vencido])>
                {{ $requerimiento->fecha_vencimiento?->format('d/m/Y') }}
                @if ($requerimiento->vencido) (vencido) @endif
            </td>
            <td class="etiqueta">Aprobado</td>
            <td>{{ $requerimiento->aprobado_at?->format('d/m/Y H:i') ?? '—' }}</td>
        </tr>
    </table>

    <h2>Descripción</h2>
    <div class="texto">{!! nl2br(e($requerimiento->descripcion)) !!}</div>

    <h2>Casos de uso ({{ $requerimiento->casosUso->count() }})</h2>
    @forelse ($requerimiento->casosUso as $casoUso)
        <div class="caso">
            <h3>{{ $casoUso->codigo }} &mdash; {{ $casoUso->nombre }}</h3>
            @if ($casoUso->pantalla || $casoUso->actor)
                <div class="sub">
                    @if ($casoUso->pantalla) Pantalla: {{ $casoUso->pantalla }} @endif
                    @if ($casoUso->pantalla && $casoUso->actor) &middot; @endif
                    @if ($casoUso->actor) Actor: {{ $casoUso->actor }} @endif
                </div>
            @endif
            @foreach (['descripcion' => 'Descripción', 'precondiciones' => 'Precondiciones', 'flujo_principal' => 'Flujo principal', 'postcondiciones' => 'Postcondiciones'] as $campo => $etiqueta)
                @if (filled($casoUso->{$campo}))
                    <div class="campo">
                        <strong>{{ $etiqueta }}</strong>
                        <div class="texto">{!! nl2br(e($casoUso->{$campo})) !!}</div>
                    </div>
                @endif
            @endforeach
        </div>
    @empty
        <p class="muted">No se han registrado casos de uso.</p>
    @endforelse

    @php
        $imagenes = $requerimiento->adjuntos->filter->esImagen();
        $documentos = $requerimiento->adjuntos->reject->esImagen();
    @endphp

    <h2>Archivos adjuntos ({{ $requerimiento->adjuntos->count() }})</h2>
    @if ($requerimiento->adjuntos->isEmpty())
        <p class="muted">Sin archivos adjuntos.</p>
    @endif

    @foreach ($imagenes as $adjunto)
        <div class="adjunto-imagen">
            @if ($dataUri = $adjunto->dataUri())
                <img src="{{ $dataUri }}" alt="{{ $adjunto->nombre_original }}">
            @else
                <p class="muted">Imagen no disponible.</p>
            @endif
            <div class="pie">{{ $adjunto->nombre_original }} ({{ $adjunto->tamanoLegible() }})</div>
        </div>
    @endforeach

    @if ($documentos->isNotEmpty())
        <table class="lista-pdf">
            @foreach ($documentos as $adjunto)
                <tr>
                    <td style="width: 70%">{{ $adjunto->nombre_original }} ({{ $adjunto->tamanoLegible() }})</td>
                    <td>
                        <a href="{{ route('requerimientos.adjuntos.descargar', [$requerimiento, $adjunto]) }}">Descargar documento</a>
                    </td>
                </tr>
            @endforeach
        </table>
        <p class="nota">Los documentos PDF solo se descargan iniciando sesión en {{ config('app.name') }} con una cuenta que tenga acceso a este requerimiento.</p>
    @endif

    <h2>Historial de revisión</h2>
    <table class="historial">
        <thead>
            <tr>
                <th style="width: 18%">Fecha</th>
                <th style="width: 22%">Usuario</th>
                <th style="width: 25%">Movimiento</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($requerimiento->revisiones as $revision)
                <tr>
                    <td>{{ $revision->created_at?->format('d/m/Y H:i') }}</td>
                    <td>{{ $revision->usuario?->name ?? 'Usuario eliminado' }}</td>
                    <td>
                        {{ $revision->etiquetaAccion() }}
                        @if ($revision->estado_anterior && $revision->estado_anterior !== $revision->estado_nuevo)
                            <div class="muted">{{ $revision->estado_anterior->label() }} &rarr; {{ $revision->estado_nuevo->label() }}</div>
                        @endif
                    </td>
                    <td>{!! $revision->observaciones ? nl2br(e($revision->observaciones)) : '<span class="muted">—</span>' !!}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">Sin movimientos.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
