@php
    $usuarioEtiqueta = $bitacora->usuario
        ? "{$bitacora->usuario->name} ({$bitacora->usuario->email})"
        : 'Invitado / sistema';
    $modeloEtiqueta = $bitacora->modelo_type
        ? class_basename($bitacora->modelo_type)." #{$bitacora->modelo_id}"
        : null;
    $clienteEtiqueta = collect([$bitacora->navegador, $bitacora->sistema_operativo, $bitacora->dispositivo])
        ->filter()
        ->implode(' · ');
    $peticion = $bitacora->metodo_http ? "{$bitacora->metodo_http} /{$bitacora->ruta}" : null;
    $camposCambio = collect(array_keys($bitacora->valores_anteriores ?? []))
        ->merge(array_keys($bitacora->valores_nuevos ?? []))
        ->unique()
        ->values();
@endphp

<table class="table table-sm align-middle mb-0">
    <tbody>
        <tr><th class="text-nowrap w-25">Fecha</th><td>{{ $bitacora->created_at?->format('d/m/Y H:i:s') ?? '—' }}</td></tr>
        <tr><th>Usuario</th><td>{{ $usuarioEtiqueta }}</td></tr>
        <tr><th>Tipo de acción</th><td>@include('admin.bitacora.partials.tipo', ['tipo' => $bitacora->tipo_accion])</td></tr>
        <tr><th>Acción</th><td>{{ $bitacora->accion }}</td></tr>
        <tr><th>Descripción</th><td>{{ $bitacora->descripcion ?: '—' }}</td></tr>
        <tr><th>Módulo</th><td>{{ $bitacora->modulo ?: '—' }}</td></tr>
        <tr><th>Registro afectado</th><td>{{ $modeloEtiqueta ?: '—' }}</td></tr>
        <tr><th>Resultado</th><td>@include('admin.bitacora.partials.resultado', ['exitoso' => $bitacora->exitoso]) {{ $bitacora->exitoso ? 'Exitoso' : 'Fallido' }}</td></tr>
        <tr><th>Error</th><td>{{ $bitacora->error_mensaje ?: '—' }}</td></tr>
        <tr><th>Código HTTP</th><td>{{ $bitacora->status_code ?? '—' }}</td></tr>
        <tr><th>Petición</th><td>@if ($peticion)<code>{{ $peticion }}</code>@else—@endif</td></tr>
        <tr><th>IP</th><td>{{ $bitacora->ip_address ?: '—' }}</td></tr>
        <tr><th>Navegador / SO / dispositivo</th><td>{{ $clienteEtiqueta ?: '—' }}</td></tr>
        <tr><th>Tiempo de ejecución</th><td>{{ $bitacora->tiempo_ejecucion !== null ? "{$bitacora->tiempo_ejecucion} ms" : '—' }}</td></tr>
        <tr><th>Etiquetas</th><td>{{ $bitacora->tags ?: '—' }}</td></tr>
    </tbody>
</table>

@if ($camposCambio->isNotEmpty())
    <h6 class="mt-4"><i class="bi bi-arrow-left-right me-1"></i> Cambios</h6>
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle">
            <thead class="text-body-secondary">
                <tr><th>Campo</th><th>Valor anterior</th><th>Valor nuevo</th></tr>
            </thead>
            <tbody>
                @foreach ($camposCambio as $campo)
                    @php
                        $anterior = $bitacora->valores_anteriores[$campo] ?? null;
                        $nuevo = $bitacora->valores_nuevos[$campo] ?? null;
                        $formatear = fn ($valor) => match (true) {
                            $valor === null || $valor === '' => '—',
                            is_array($valor) => json_encode($valor, JSON_UNESCAPED_UNICODE),
                            default => (string) $valor,
                        };
                    @endphp
                    <tr>
                        <th class="text-nowrap">{{ $campo }}</th>
                        <td class="text-danger-emphasis">{{ $formatear($anterior) }}</td>
                        <td class="text-success-emphasis">{{ $formatear($nuevo) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@if ($bitacora->datos_adicionales)
    <h6 class="mt-4"><i class="bi bi-info-circle me-1"></i> Datos adicionales</h6>
    <pre class="bg-body-tertiary p-2 rounded small mb-0">{{ json_encode($bitacora->datos_adicionales, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
@endif

@if ($bitacora->user_agent)
    <p class="small text-muted mt-3 mb-0 text-break">{{ $bitacora->user_agent }}</p>
@endif
