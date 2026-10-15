@extends('layouts.app')

@section('title', $proyecto->codigo)

@php
    $puedeAdjuntos = auth()->user()->can('gestionarAdjuntos', $proyecto) && ! $proyecto->estado->estaCerrado();
    $urlDetalle = route('proyectos.show', $proyecto);
    $ultimaCancelacion = $proyecto->estado === \App\Enums\EstadoProyecto::Cancelado
        ? $proyecto->revisiones->firstWhere('accion', 'cancelar')
        : null;
@endphp

@section('content')
    <x-page-header :title="$proyecto->codigo.' · '.$proyecto->nombre" icon="briefcase" section="Proyectos"
                   :parent="['url' => route('proyectos.index'), 'label' => 'Proyectos']">
        <x-page-action-group label="Consultas">
            <x-action-btn variant="nav" icon="diagram-2" label="Diagrama" data-bs-toggle="modal" data-bs-target="#diagrama-modal" />
            @can('viewAny', \App\Models\ProyectoEvaluacionPf::class)
                <x-action-btn variant="tool" icon="calculator" label="Puntos de función" :href="route('proyectos.evaluaciones-pf.index', $proyecto)" />
            @endcan
            @can('export', $proyecto)
                <x-action-btn variant="export" icon="file-earmark-pdf" label="Exportar PDF" :href="route('proyectos.pdf', $proyecto)" target="_blank" />
            @endcan
        </x-page-action-group>
        @can('update', $proyecto)
            <x-page-action-group label="Edición">
                <x-action-btn variant="edit" icon="pencil-square" label="Editar" :href="route('proyectos.edit', $proyecto)" />
            </x-page-action-group>
        @endcan
        @include('proyectos.partials.botones-flujo')
    </x-page-header>

    @if ($ultimaCancelacion)
        <div class="alert alert-secondary d-flex gap-2" role="alert">
            <i class="bi bi-slash-circle fs-5"></i>
            <div>
                <strong>Cancelado por {{ $ultimaCancelacion->usuario?->name ?? 'un usuario' }}</strong>
                el {{ $ultimaCancelacion->created_at?->format('d/m/Y H:i') }}.
                <div class="mt-1">{!! nl2br(e($ultimaCancelacion->observaciones)) !!}</div>
            </div>
        </div>
    @elseif ($proyecto->estado === \App\Enums\EstadoProyecto::Activo && $pendientes > 0 && $acciones->contains('finalizar'))
        <div class="alert alert-warning d-flex gap-2" role="alert">
            <i class="bi bi-exclamation-triangle fs-5"></i>
            <div>
                {{ \App\Services\ProyectoFlujoService::mensajePendientes($pendientes) }}
                El proyecto podrá cerrarse cuando todos sus requerimientos estén finalizados.
            </div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header"><i class="bi bi-card-text me-1"></i> Descripción</div>
                <div class="card-body">
                    <div class="requerimiento-descripcion">{!! nl2br(e($proyecto->descripcion)) !!}</div>
                </div>
            </div>

            <div class="card mb-4" id="requerimientos">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <span>
                        <i class="bi bi-clipboard-check me-1"></i> Requerimientos del proyecto ({{ $proyecto->requerimientos->count() }})
                    </span>
                    <div class="d-flex align-items-center gap-2">
                        @can('create', \App\Models\Requerimiento::class)
                            @if ($proyecto->estado === \App\Enums\EstadoProyecto::Activo)
                                <a href="{{ route('requerimientos.create', ['proyecto_id' => $proyecto->id]) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus-lg"></i> Nuevo requerimiento
                                </a>
                            @endif
                        @endcan
                    @if ($proyecto->requerimientos->isNotEmpty())
                        <span @class(['badge', 'text-bg-warning' => $pendientes > 0, 'text-bg-success' => $pendientes === 0])>
                            {{ $pendientes > 0 ? $pendientes.' sin finalizar' : 'Todos finalizados' }}
                        </span>
                    @endif
                    </div>
                </div>
                <div class="card-body">
                    @if ($proyecto->requerimientos->isEmpty())
                        <p class="text-body-secondary mb-0">
                            Aún no hay requerimientos en este proyecto. Use el botón «Nuevo requerimiento» para crear uno.
                        </p>
                    @else
                        @if ($requerimientosOcultos > 0)
                            <p class="small text-body-secondary">{{ $requerimientosOcultos }} requerimiento(s) no se muestran por permisos de confidencialidad.</p>
                        @endif
                        <div class="table-responsive">
                            <table class="table table-sm align-middle small mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col">Código</th>
                                        <th scope="col">Título</th>
                                        <th scope="col">Solicitante</th>
                                        <th scope="col">Gestor</th>
                                        <th scope="col">Estado</th>
                                        <th scope="col">Vencimiento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($requerimientosVisibles as $requerimiento)
                                        <tr>
                                            <td class="text-nowrap">
                                                @include('requerimientos.partials.codigo')
                                            </td>
                                            <td>{{ $requerimiento->titulo }}</td>
                                            <td>{{ $requerimiento->solicitante?->name }}</td>
                                            <td>{{ $requerimiento->gestor?->name ?? 'Sin asignar' }}</td>
                                            <td>@include('requerimientos.partials.estado', ['estado' => $requerimiento->estado])</td>
                                            <td class="text-nowrap">@include('requerimientos.partials.vencimiento')</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mb-4" id="adjuntos">
                <div class="card-header">
                    <i class="bi bi-paperclip me-1"></i> Documentos adjuntos ({{ $proyecto->adjuntos->count() }})
                </div>
                <div class="card-body">
                    @if ($proyecto->adjuntos->isNotEmpty())
                        <div class="adjuntos-grid">
                            @foreach ($proyecto->adjuntos as $adjunto)
                                @php($urlArchivo = route('proyectos.adjuntos.show', [$proyecto, $adjunto]))
                                <div class="adjunto-item">
                                    <button type="button" class="adjunto-item__vista"
                                            data-visor
                                            data-visor-url="{{ $urlArchivo }}"
                                            data-visor-descargar="{{ route('proyectos.adjuntos.descargar', [$proyecto, $adjunto]) }}"
                                            data-visor-tipo="{{ $adjunto->esImagen() ? 'imagen' : 'pdf' }}"
                                            data-visor-nombre="{{ $adjunto->nombre_original }}"
                                            title="Ver {{ $adjunto->nombre_original }}">
                                        @if ($adjunto->esImagen())
                                            <img src="{{ $urlArchivo }}" alt="{{ $adjunto->nombre_original }}" loading="lazy">
                                        @else
                                            <i class="bi bi-file-earmark-pdf text-danger" aria-hidden="true"></i>
                                        @endif
                                    </button>
                                    <div class="adjunto-item__info">
                                        <div class="text-truncate small fw-semibold" title="{{ $adjunto->nombre_original }}">{{ $adjunto->nombre_original }}</div>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="text-body-secondary small">{{ $adjunto->tamanoLegible() }}</span>
                                            @if ($puedeAdjuntos)
                                                <x-delete-button :url="route('proyectos.adjuntos.destroy', [$proyecto, $adjunto])" size="sm"
                                                                 class="border-0"
                                                                 :confirm="'¿Eliminar el archivo '.$adjunto->nombre_original.'?'"
                                                                 :redirect="$urlDetalle.'#adjuntos'" />
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-body-secondary mb-0">Sin documentos adjuntos.</p>
                    @endif

                    @if ($puedeAdjuntos && $proyecto->adjuntos->count() < \App\Http\Requests\Proyectos\ProyectoRequest::MAX_ADJUNTOS)
                        <div id="adjuntos-subida"
                             class="dropzone photo-dropzone adjuntos-dropzone mt-3"
                             data-url="{{ route('proyectos.adjuntos.store', $proyecto) }}"
                             data-max-files="{{ \App\Http\Requests\Proyectos\ProyectoRequest::MAX_ADJUNTOS - $proyecto->adjuntos->count() }}"
                             data-max-filesize="{{ \App\Http\Requests\Requerimientos\AdjuntoRequest::MAX_KB / 1024 }}"></div>
                        <div class="form-text">PNG, JPG o PDF. Los archivos se suben al soltarlos.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-calculator me-1"></i> Puntos de función</span>
                    <a href="{{ route('proyectos.evaluaciones-pf.index', $proyecto) }}" class="small">Ver todas</a>
                </div>
                <div class="card-body small">
                    @if ($ultimaEvaluacionPf)
                        <p class="mb-2 fw-semibold">{{ $ultimaEvaluacionPf->nombre }}</p>
                        <dl class="row mb-0">
                            <dt class="col-7">PF sin ajuste</dt>
                            <dd class="col-5 text-end">{{ $ultimaEvaluacionPf->total_sin_ajuste }}</dd>
                            <dt class="col-7">PF ajustados</dt>
                            <dd class="col-5 text-end fw-bold text-primary">{{ number_format($ultimaEvaluacionPf->total_ajustado, 2, ',', '.') }}</dd>
                            @if ($diagnosticoPfUltima)
                                <dt class="col-7">Viabilidad</dt>
                                <dd class="col-5 text-end">
                                    <x-viabilidad-pf-badge :viabilidad="$diagnosticoPfUltima['viabilidad']" :short="true" />
                                </dd>
                                <dt class="col-7">Tiempo est.</dt>
                                <dd class="col-5 text-end">{{ $diagnosticoPfUltima['tiempo']['dias_laborables'] }} días lab.</dd>
                            @endif
                            <dt class="col-7">Actualizada</dt>
                            <dd class="col-5 text-end">{{ $ultimaEvaluacionPf->updated_at?->format('d/m/Y') }}</dd>
                        </dl>
                        <a href="{{ route('proyectos.evaluaciones-pf.show', [$proyecto, $ultimaEvaluacionPf]) }}" class="btn btn-sm btn-outline-secondary mt-3 w-100">Abrir evaluación</a>
                    @else
                        <p class="text-body-secondary mb-0">Sin evaluación IFPUG registrada.</p>
                        @can('create', [\App\Models\ProyectoEvaluacionPf::class, $proyecto])
                            <a href="{{ route('proyectos.evaluaciones-pf.create', $proyecto) }}" class="btn btn-sm btn-primary mt-3 w-100">Nueva evaluación</a>
                        @endcan
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><i class="bi bi-info-circle me-1"></i> Datos generales</div>
                <div class="card-body">
                    <dl class="row small mb-0">
                        <dt class="col-5">Estado</dt>
                        <dd class="col-7">@include('proyectos.partials.estado', ['estado' => $proyecto->estado])</dd>
                        <dt class="col-5">Responsable</dt>
                        <dd class="col-7">{{ $proyecto->responsable?->name }}</dd>
                        <dt class="col-5">Finalización</dt>
                        <dd class="col-7">
                            @include('proyectos.partials.fecha-finalizacion')
                            @if ($proyecto->vencido)
                                <span class="badge text-bg-danger ms-1">Vencido</span>
                            @endif
                        </dd>
                        <dt class="col-5">Creado por</dt>
                        <dd class="col-7">{{ $proyecto->creador?->name ?? '—' }}</dd>
                        <dt class="col-5">Creado</dt>
                        <dd class="col-7">{{ $proyecto->created_at?->format('d/m/Y H:i') }}</dd>
                        @if ($proyecto->finalizado_at)
                            <dt class="col-5">Finalizado</dt>
                            <dd class="col-7">{{ $proyecto->finalizado_at->format('d/m/Y H:i') }}</dd>
                        @endif
                        @if ($proyecto->cancelado_at)
                            <dt class="col-5">Cancelado</dt>
                            <dd class="col-7">{{ $proyecto->cancelado_at->format('d/m/Y H:i') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><i class="bi bi-clock-history me-1"></i> Historial</div>
                <div class="card-body">
                    @if ($proyecto->revisiones->isEmpty())
                        <p class="text-body-secondary mb-0">Sin movimientos registrados.</p>
                    @else
                        <ol class="sesion-timeline mb-0">
                            @foreach ($proyecto->revisiones as $revision)
                                <li @class(['sesion-timeline__item', 'sesion-timeline__item--error' => $revision->accion === 'cancelar'])>
                                    <span class="sesion-timeline__marker" aria-hidden="true"></span>
                                    <div class="sesion-timeline__time">{{ $revision->created_at?->format('d/m/Y H:i') }}</div>
                                    <div class="sesion-timeline__title">{{ $revision->etiquetaAccion() }}</div>
                                    <div class="sesion-timeline__meta">
                                        {{ $revision->usuario?->name ?? 'Usuario eliminado' }}
                                        @if ($revision->estado_anterior && $revision->estado_anterior !== $revision->estado_nuevo)
                                            · {{ $revision->estado_anterior->label() }} <i class="bi bi-arrow-right"></i> {{ $revision->estado_nuevo->label() }}
                                        @endif
                                    </div>
                                    @if ($revision->observaciones)
                                        <div class="small mt-1 p-2 rounded bg-body-tertiary">{!! nl2br(e($revision->observaciones)) !!}</div>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('proyectos.partials.diagrama')
    @include('requerimientos.partials.visor-adjunto')
@endsection

@push('scripts')
    @vite('resources/js/pages/proyecto-show.js')
@endpush
