<div class="card mb-4" id="diagrama">
    <div class="card-header">
        <i class="bi bi-diagram-2 me-1"></i> Diagrama de estados
    </div>
    <div class="card-body">
        @if ($diagrama['pasos'] === [])
            <p class="text-body-secondary mb-0">Este requerimiento aún no tiene pasos registrados.</p>
        @else
            <p class="small text-body-secondary">
                Máquina de estados del requerimiento. Las transiciones recorridas llevan el evento y el número de paso; las punteadas son transiciones posibles que aún no ocurren.
            </p>
            <div id="diagrama-estados" class="diagrama-estados" role="img" aria-label="Diagrama de estados UML del requerimiento"></div>
            <script type="application/json" id="diagrama-estados-datos">@json($diagrama)</script>

            <div class="table-responsive mt-3">
                <table class="table table-sm align-middle small mb-0">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Transición</th>
                            <th scope="col">Evento</th>
                            <th scope="col">Actor</th>
                            <th scope="col">Fecha</th>
                            <th scope="col">Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($diagrama['pasos'] as $paso)
                            <tr @class(['table-active' => $paso['actual']])>
                                <td>{{ $paso['numero'] }}</td>
                                <td class="text-nowrap">{{ $paso['origenNombre'] }} <i class="bi bi-arrow-right"></i> {{ $paso['etapa'] }}</td>
                                <td>{{ $paso['accion'] }}</td>
                                <td>
                                    <span @class(['diagrama-leyenda', 'diagrama-leyenda--gestor' => $paso['rol'] === 'Gestor', 'diagrama-leyenda--solicitante' => $paso['rol'] === 'Solicitante'])></span>
                                    {{ $paso['actor'] }}
                                    <span class="text-body-secondary">
                                        ({{ $paso['rol'] }}{{ $paso['mismoSolicitante'] ? ', también es el solicitante' : '' }})
                                    </span>
                                </td>
                                <td class="text-nowrap">{{ $paso['fecha'] }}</td>
                                <td @class(['text-danger' => $paso['rechazo'], 'text-body-secondary' => ! $paso['rechazo']])>{{ $paso['observaciones'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
