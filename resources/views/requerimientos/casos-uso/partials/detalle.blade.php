<article @class(['caso-uso', 'border-top pt-3 mt-3' => ! $loop->first])>
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
        <div>
            <h3 class="h6 mb-1">
                <span class="badge text-bg-light border me-1">{{ $casoUso->codigo }}</span>
                {{ $casoUso->nombre }}
            </h3>
            <div class="small text-body-secondary">
                @if ($casoUso->pantalla)
                    <span class="me-3"><i class="bi bi-window me-1"></i>{{ $casoUso->pantalla }}</span>
                @endif
                @if ($casoUso->actor)
                    <span><i class="bi bi-person me-1"></i>{{ $casoUso->actor }}</span>
                @endif
            </div>
        </div>
        @if ($puedeCasosUso)
            <div class="btn-group btn-group-sm">
                <a href="{{ route('requerimientos.casos-uso.edit', [$requerimiento, $casoUso]) }}" class="btn btn-outline-primary" title="Editar">
                    <i class="bi bi-pencil-square"></i>
                </a>
                <x-delete-button :url="route('requerimientos.casos-uso.destroy', [$requerimiento, $casoUso])"
                                 :confirm="'¿Eliminar el caso de uso '.$casoUso->codigo.'?'"
                                 :redirect="$urlDetalle.'#casos-uso'" />
            </div>
        @endif
    </div>

    @foreach (['descripcion' => 'Descripción', 'precondiciones' => 'Precondiciones', 'flujo_principal' => 'Flujo principal', 'postcondiciones' => 'Postcondiciones'] as $campo => $etiqueta)
        @if (filled($casoUso->{$campo}))
            <div class="mt-2">
                <div class="small fw-semibold text-body-secondary">{{ $etiqueta }}</div>
                <div class="small">{!! nl2br(e($casoUso->{$campo})) !!}</div>
            </div>
        @endif
    @endforeach
</article>
