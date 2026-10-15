@php
    $variantes = [
        'finalizar' => ['success', 'flag'],
        'cancelar' => ['cancel', 'slash-circle'],
    ];
@endphp

@if ($acciones->isNotEmpty())
    <x-page-action-group label="Estado del proyecto">
        @foreach ($acciones as $accion)
            @php([$variante, $icono] = $variantes[$accion])
            <form method="POST" action="{{ route('proyectos.flujo', [$proyecto, $accion]) }}" class="page-action-form"
                  @if ($accion === 'cancelar')
                      data-observaciones="requerida"
                      data-observaciones-titulo="Cancelar {{ $proyecto->codigo }}"
                      data-observaciones-etiqueta="Motivo de la cancelación (obligatorio)"
                      data-observaciones-placeholder="Explique por qué se cancela el proyecto..."
                      data-observaciones-boton="Cancelar proyecto"
                      data-observaciones-mensaje="El motivo es obligatorio para cancelar."
                  @elseif ($pendientes > 0)
                      data-bloqueo="{{ \App\Services\ProyectoFlujoService::mensajePendientes($pendientes) }} Finalícelos o elimínelos antes de cerrar el proyecto."
                      data-bloqueo-titulo="No se puede finalizar"
                  @else
                      data-confirm="El proyecto quedará cerrado y ya no recibirá requerimientos."
                      data-confirm-title="¿Finalizar el proyecto?"
                      data-confirm-button="Sí, finalizar"
                  @endif>
                @csrf
                <input type="hidden" name="observaciones" value="">
                <x-action-btn
                    type="submit"
                    :variant="$variante"
                    :icon="$icono"
                    :label="\App\Services\ProyectoFlujoService::ETIQUETAS[$accion]"
                />
            </form>
        @endforeach
    </x-page-action-group>
@endif
