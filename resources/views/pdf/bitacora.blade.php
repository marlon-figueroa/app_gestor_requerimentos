<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Bitácora</title>
    <style>
        @page { margin: 90px 30px 60px 30px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #212529; }
        header { position: fixed; top: -70px; left: 0; right: 0; height: 60px; border-bottom: 2px solid #0d6efd; }
        header h1 { margin: 0; font-size: 16px; color: #0d6efd; }
        header .meta { font-size: 9px; color: #6c757d; }
        footer { position: fixed; bottom: -40px; left: 0; right: 0; height: 30px; font-size: 8px; color: #6c757d; text-align: right; }
        footer .page:after { content: counter(page); }
        .filtros { margin-bottom: 8px; }
        .filtros span { display: inline-block; background: #e9ecef; padding: 2px 6px; border-radius: 3px; margin-right: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #0d6efd; color: #fff; text-align: left; padding: 5px; }
        td { padding: 4px 5px; border-bottom: 1px solid #dee2e6; vertical-align: top; }
        tr:nth-child(even) td { background: #f8f9fa; }
        .ok { color: #198754; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .text-center { text-align: center; }
        .aviso { color: #997404; margin-top: 6px; }
    </style>
</head>
<body>
    <header>
        <h1>{{ config('app.name') }} &mdash; Bitácora</h1>
        <div class="meta">Generado el {{ now()->format('d/m/Y H:i') }} por {{ auth()->user()->name }}</div>
    </header>

    <footer>
        Página <span class="page"></span>
    </footer>

    @if ($filtros)
        <div class="filtros">
            <strong>Filtros:</strong>
            @foreach ($filtros as $etiqueta => $valor)
                <span>{{ $etiqueta }}: {{ $valor }}</span>
            @endforeach
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 11%">Fecha</th>
                <th style="width: 14%">Usuario</th>
                <th style="width: 10%">Tipo</th>
                <th style="width: 9%">Módulo</th>
                <th style="width: 16%">Acción</th>
                <th style="width: 25%">Descripción</th>
                <th style="width: 10%">IP</th>
                <th style="width: 5%">Res.</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($registros as $registro)
                <tr>
                    <td>{{ $registro->created_at?->format('d/m/Y H:i:s') }}</td>
                    <td>{{ $registro->usuario?->name ?? 'Invitado / sistema' }}</td>
                    <td>{{ \App\Models\Bitacora::etiquetaTipoAccion($registro->tipo_accion) }}</td>
                    <td>{{ $registro->modulo }}</td>
                    <td>{{ $registro->accion }}</td>
                    <td>{{ $registro->descripcion }}</td>
                    <td>{{ $registro->ip_address }}</td>
                    <td class="text-center">
                        @if ($registro->exitoso)
                            <span class="ok">OK</span>
                        @else
                            <span class="error">Falló</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No hay registros que coincidan con los filtros.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p style="margin-top: 8px;"><strong>Total:</strong> {{ $registros->count() }} registro(s)</p>
    @if ($registros->count() >= $limite)
        <p class="aviso">Se muestran solo los {{ $limite }} registros más recientes; refine los filtros para acotar el reporte.</p>
    @endif
</body>
</html>
