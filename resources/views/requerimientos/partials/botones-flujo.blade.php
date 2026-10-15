@php
    $estilos = [
        'enviar' => ['primary', 'send'],
        'tomar' => ['warning', 'hand-index'],
        'aprobar' => ['success', 'check-circle'],
        'rechazar' => ['danger', 'x-circle'],
        'iniciar' => ['primary', 'play-circle'],
        'finalizar' => ['success', 'flag'],
        'liberar' => ['outline-secondary', 'box-arrow-left'],
        'reasignar' => ['outline-warning', 'person-lines-fill'],
    ];
    $confirmaciones = [
        'enviar' => 'Mientras esté en revisión no podrá modificarlo.',
        'tomar' => 'Quedará asignado como gestor de este requerimiento.',
        'iniciar' => 'El requerimiento pasará a desarrollo.',
        'finalizar' => 'El requerimiento se marcará como finalizado.',
    ];
@endphp

@php($gestoresJson = isset($gestores) ? json_encode($gestores) : '{}')
@foreach ($acciones as $accion)
    @php([$color, $icono] = $estilos[$accion] ?? ['secondary', 'arrow-right'])
    <form method="POST" action="{{ route('requerimientos.flujo', [$requerimiento, $accion]) }}" class="d-inline"
          @if ($accion === 'reasignar') data-reasignar-gestor data-gestores="{{ e($gestoresJson) }}" data-observaciones-titulo="Reasignar {{ $requerimiento->codigo }}" @endif
          @if (in_array($accion, ['aprobar', 'rechazar'], true))
              data-observaciones="{{ $accion === 'rechazar' ? 'requerida' : 'opcional' }}"
              data-observaciones-titulo="{{ \App\Services\RequerimientoFlujoService::ETIQUETAS[$accion] }} {{ $requerimiento->codigo }}"
          @else
              data-confirm="{{ $confirmaciones[$accion] }}"
              data-confirm-title="¿{{ \App\Services\RequerimientoFlujoService::ETIQUETAS[$accion] }}?"
              data-confirm-button="Sí, continuar"
          @endif>
        @csrf
        <input type="hidden" name="observaciones" value="">
        <button type="submit" class="btn btn-{{ $color }}">
            <i class="bi bi-{{ $icono }}"></i> {{ \App\Services\RequerimientoFlujoService::ETIQUETAS[$accion] }}
        </button>
    </form>
@endforeach
