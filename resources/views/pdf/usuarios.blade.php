<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Listado de usuarios</title>
    <style>
        @page { margin: 90px 40px 60px 40px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #212529; }
        header { position: fixed; top: -70px; left: 0; right: 0; height: 60px; border-bottom: 2px solid {{ config('branding.primary') }}; }
        header h1 { margin: 0; font-size: 18px; color: {{ config('branding.primary') }}; }
        header .meta { font-size: 10px; color: #6c757d; }
        footer { position: fixed; bottom: -40px; left: 0; right: 0; height: 30px; font-size: 9px; color: #6c757d; text-align: right; }
        footer .page:after { content: counter(page); }
        .filtros { margin-bottom: 10px; font-size: 10px; }
        .filtros span { display: inline-block; background: #e9ecef; padding: 2px 6px; border-radius: 3px; margin-right: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: {{ config('branding.primary') }}; color: #fff; text-align: left; padding: 6px; }
        td { padding: 5px 6px; border-bottom: 1px solid #dee2e6; }
        tr:nth-child(even) td { background: #f8f9fa; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <header>
        <h1>{{ config('app.name') }} &mdash; Listado de usuarios</h1>
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
                <th style="width: 6%">ID</th>
                <th style="width: 28%">Nombre</th>
                <th style="width: 30%">Correo electrónico</th>
                <th style="width: 20%">Roles</th>
                <th style="width: 16%">Fecha de alta</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->getRoleNames()->implode(', ') ?: 'Sin asignar' }}</td>
                    <td>{{ $user->created_at?->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No hay usuarios que coincidan con los filtros.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p style="margin-top: 10px;"><strong>Total:</strong> {{ $users->count() }} usuario(s)</p>
</body>
</html>
