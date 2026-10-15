@php
    $variantes = [
        'enviar' => ['success', 'send'],
        'tomar' => ['warning', 'hand-index'],
        'aprobar' => ['success', 'check-circle'],
        'rechazar' => ['danger', 'x-circle'],
        'iniciar' => ['edit', 'play-circle'],
        'finalizar' => ['success', 'flag'],
        'liberar' => ['cancel', 'box-arrow-left'],
        'reasignar' => ['warning', 'person-lines-fill'],
    ];
    $confirmaciones = [
        'enviar' => 'Mientras esté en revisión no podrá modificarlo.',
        'tomar' => 'Quedará asignado como gestor de este requerimiento.',
        'iniciar' => 'El requerimiento pasará a desarrollo.',
        'finalizar' => 'El requerimiento se marcará como finalizado.',
        'liberar' => 'El requerimiento volverá a la bandeja sin gestor asignado.',
    ];
    $gestoresJson = isset($gestores) ? json_encode($gestores) : '{}';
@endphp

@if ($acciones->isNotEmpty())
    <x-page-action-group label="Flujo de trabajo">
        @foreach ($acciones as $accion)
            @php([$variante, $icono] = $variantes[$accion] ?? ['nav', 'arrow-right'])
            <form method="POST" action="{{ route('requerimientos.flujo', [$requerimiento, $accion]) }}" class="page-action-form"
                  @if ($accion === 'reasignar') data-reasignar-gestor data-gestores="{{ e($gestoresJson) }}" data-observaciones-titulo="Reasignar {{ $requerimiento->codigo }}" @endif
                  @if (in_array($accion, ['aprobar', 'rechazar'], true))
                      data-observaciones="{{ $accion === 'rechazar' ? 'requerida' : 'opcional' }}"
                      data-observaciones-titulo="{{ \App\Services\RequerimientoFlujoService::ETIQUETAS[$accion] }} {{ $requerimiento->codigo }}"
                  @elseif (isset($confirmaciones[$accion]))
                      data-confirm="{{ $confirmaciones[$accion] }}"
                      data-confirm-title="¿{{ \App\Services\RequerimientoFlujoService::ETIQUETAS[$accion] }}?"
                      data-confirm-button="Sí, continuar"
                  @endif>
                @csrf
                <input type="hidden" name="observaciones" value="">
                <x-action-btn
                    type="submit"
                    :variant="$variante"
                    :icon="$icono"
                    :label="\App\Services\RequerimientoFlujoService::ETIQUETAS[$accion]"
                />
            </form>
        @endforeach
    </x-page-action-group>
@endif
