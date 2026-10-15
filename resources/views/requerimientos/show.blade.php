@extends('layouts.app')

@section('title', $requerimiento->codigo)

@php
    $puedeCasosUso = auth()->user()->can('gestionarCasosUso', $requerimiento);
    $puedeAdjuntos = auth()->user()->can('gestionarAdjuntos', $requerimiento);
    $urlDetalle = route('requerimientos.show', $requerimiento);
@endphp

@section('content')
    <x-page-header :title="$requerimiento->codigo.' · '.$requerimiento->titulo" icon="clipboard-check" section="Requerimientos"
                   :parent="['url' => route('requerimientos.index'), 'label' => 'Requerimientos']">
        @can('export', $requerimiento)
            <a href="{{ route('requerimientos.pdf', $requerimiento) }}" class="btn btn-outline-danger" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
            </a>
        @endcan
        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#diagrama-modal">
            <i class="bi bi-diagram-2"></i> Diagrama de estados
        </button>
        @can('update', $requerimiento)
            <a href="{{ route('requerimientos.edit', $requerimiento) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil-square"></i> Editar
            </a>
        @endcan
        @can('delete', $requerimiento)
            <x-delete-button :url="route('requerimientos.destroy', $requerimiento)" label="Eliminar"
                             :confirm="'¿Eliminar el requerimiento '.$requerimiento->codigo.'?'"
                             :redirect="route('requerimientos.index')" />
        @endcan
        @include('requerimientos.partials.botones-flujo', [
            'gestores' => \App\Http\Requests\Proyectos\ProyectoRequest::responsables()->pluck('name', 'id'),
        ])
    </x-page-header>

    @php
        $ultimoRechazo = $requerimiento->estado === \App\Enums\EstadoRequerimiento::Rechazado
            ? $requerimiento->revisiones->firstWhere('accion', 'rechazar')
            : null;
    @endphp
    @if ($ultimoRechazo)
        <div class="alert alert-danger d-flex gap-2" role="alert">
            <i class="bi bi-exclamation-octagon fs-5"></i>
            <div>
                <strong>Rechazado por {{ $ultimoRechazo->usuario?->name ?? 'un gestor' }}</strong>
                el {{ $ultimoRechazo->created_at?->format('d/m/Y H:i') }}.
                <div class="mt-1">{!! nl2br(e($ultimoRechazo->observaciones)) !!}</div>
                @can('enviar', $requerimiento)
                    <div class="small mt-2">Corrija lo indicado y vuelva a enviarlo a revisión.</div>
                @endcan
            </div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header"><i class="bi bi-card-text me-1"></i> Descripción</div>
                <div class="card-body">
                    <div class="requerimiento-descripcion">{!! nl2br(e($requerimiento->descripcion)) !!}</div>
                </div>
            </div>

            <div class="card mb-4" id="casos-uso">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-diagram-3 me-1"></i> Casos de uso ({{ $requerimiento->casosUso->count() }})</span>
                    @if ($puedeCasosUso)
                        <a href="{{ route('requerimientos.casos-uso.create', $requerimiento) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-lg"></i> Nuevo caso de uso
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    @forelse ($requerimiento->casosUso as $casoUso)
                        @include('requerimientos.casos-uso.partials.detalle')
                    @empty
                        <p class="text-body-secondary mb-0">
                            Aún no se han identificado casos de uso. Cada caso de uso describe una parte o pantalla con su funcionalidad.
                        </p>
                    @endforelse
                </div>
            </div>

            <div class="card mb-4" id="comentarios">
                <div class="card-header"><i class="bi bi-chat-left-text me-1"></i> Comentarios ({{ $requerimiento->comentarios->count() }})</div>
                <div class="card-body">
                    @forelse ($requerimiento->comentarios as $comentario)
                        <div @class(['mb-3 pb-3', 'border-bottom' => ! $loop->last])>
                            <div class="small text-body-secondary">
                                <strong>{{ $comentario->usuario?->name }}</strong>
                                · {{ $comentario->created_at?->format('d/m/Y H:i') }}
                            </div>
                            <div class="mt-1">{!! nl2br(e($comentario->cuerpo)) !!}</div>
                        </div>
                    @empty
                        <p class="text-body-secondary mb-3">Sin comentarios aún.</p>
                    @endforelse

                    @can('view', $requerimiento)
                        <form method="POST" action="{{ route('requerimientos.comentarios.store', $requerimiento) }}" class="mt-2">
                            @csrf
                            <label for="cuerpo-comentario" class="form-label">Agregar comentario</label>
                            <textarea id="cuerpo-comentario" name="cuerpo" class="form-control @error('cuerpo') is-invalid @enderror" rows="3" required maxlength="5000">{{ old('cuerpo') }}</textarea>
                            @error('cuerpo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <button type="submit" class="btn btn-sm btn-primary mt-2">Publicar</button>
                        </form>
                    @endcan
                </div>
            </div>

            <div class="card mb-4" id="adjuntos">
                <div class="card-header">
                    <i class="bi bi-paperclip me-1"></i> Archivos adjuntos ({{ $requerimiento->adjuntos->count() }})
                </div>
                <div class="card-body">
                    @if ($requerimiento->adjuntos->isNotEmpty())
                        <div class="adjuntos-grid">
                            @foreach ($requerimiento->adjuntos as $adjunto)
                                @php($urlArchivo = route('requerimientos.adjuntos.show', [$requerimiento, $adjunto]))
                                <div class="adjunto-item">
                                    <button type="button" class="adjunto-item__vista"
                                            data-visor
                                            data-visor-url="{{ $urlArchivo }}"
                                            data-visor-descargar="{{ route('requerimientos.adjuntos.descargar', [$requerimiento, $adjunto]) }}"
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
                                                <x-delete-button :url="route('requerimientos.adjuntos.destroy', [$requerimiento, $adjunto])" size="sm"
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
                        <p class="text-body-secondary mb-0">Sin archivos adjuntos.</p>
                    @endif

                    @if ($puedeAdjuntos && $requerimiento->adjuntos->count() < \App\Http\Requests\Requerimientos\RequerimientoRequest::MAX_ADJUNTOS)
                        <div id="adjuntos-subida"
                             class="dropzone photo-dropzone adjuntos-dropzone mt-3"
                             data-url="{{ route('requerimientos.adjuntos.store', $requerimiento) }}"
                             data-max-files="{{ \App\Http\Requests\Requerimientos\RequerimientoRequest::MAX_ADJUNTOS - $requerimiento->adjuntos->count() }}"
                             data-max-filesize="{{ \App\Http\Requests\Requerimientos\AdjuntoRequest::MAX_KB / 1024 }}"></div>
                        <div class="form-text">PNG, JPG o PDF. Los archivos se suben al soltarlos.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header"><i class="bi bi-info-circle me-1"></i> Datos generales</div>
                <div class="card-body">
                    <dl class="row small mb-0">
                        <dt class="col-5">Estado</dt>
                        <dd class="col-7">@include('requerimientos.partials.estado', ['estado' => $requerimiento->estado])</dd>
                        <dt class="col-5">Prioridad</dt>
                        <dd class="col-7">@include('requerimientos.partials.prioridad', ['prioridad' => $requerimiento->prioridad])</dd>
                        <dt class="col-5">Proyecto</dt>
                        <dd class="col-7">
                            @if ($requerimiento->proyecto)
                                @can('view', $requerimiento->proyecto)
                                    <a href="{{ route('proyectos.show', $requerimiento->proyecto) }}" class="text-decoration-none">
                                        {{ $requerimiento->proyecto->codigo }} · {{ $requerimiento->proyecto->nombre }}
                                    </a>
                                @else
                                    {{ $requerimiento->proyecto->codigo }} · {{ $requerimiento->proyecto->nombre }}
                                @endcan
                            @else
                                <span class="text-body-secondary">Sin proyecto</span>
                            @endif
                        </dd>
                        <dt class="col-5">Solicitante</dt>
                        <dd class="col-7">{{ $requerimiento->solicitante?->name }}</dd>
                        <dt class="col-5">Gestor</dt>
                        <dd class="col-7">
                            @if ($requerimiento->gestor)
                                {{ $requerimiento->gestor->name }}
                            @elseif ($requerimiento->enBandeja())
                                <span class="text-warning-emphasis">En bandeja, sin tomar</span>
                            @else
                                <span class="text-body-secondary">Se asigna al revisarlo</span>
                            @endif
                        </dd>
                        <dt class="col-5">Solicitud</dt>
                        <dd class="col-7">{{ $requerimiento->fecha_solicitud?->format('d/m/Y') }}</dd>
                        <dt class="col-5">Vencimiento</dt>
                        <dd class="col-7">
                            @include('requerimientos.partials.vencimiento')
                            @if ($requerimiento->vencido)
                                <span class="badge text-bg-danger ms-1">Vencido</span>
                            @endif
                        </dd>
                        @if ($requerimiento->enviado_at)
                            <dt class="col-5">Enviado</dt>
                            <dd class="col-7">{{ $requerimiento->enviado_at->format('d/m/Y H:i') }}</dd>
                        @endif
                        @if ($requerimiento->aprobado_at)
                            <dt class="col-5">Aprobado</dt>
                            <dd class="col-7">{{ $requerimiento->aprobado_at->format('d/m/Y H:i') }}</dd>
                        @endif
                        @if ($requerimiento->finalizado_at)
                            <dt class="col-5">Finalizado</dt>
                            <dd class="col-7">{{ $requerimiento->finalizado_at->format('d/m/Y H:i') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><i class="bi bi-clock-history me-1"></i> Historial</div>
                <div class="card-body">
                    @if ($requerimiento->revisiones->isEmpty())
                        <p class="text-body-secondary mb-0">Sin movimientos registrados.</p>
                    @else
                        <ol class="sesion-timeline mb-0">
                            @foreach ($requerimiento->revisiones as $revision)
                                <li @class(['sesion-timeline__item', 'sesion-timeline__item--error' => $revision->accion === 'rechazar'])>
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

    @include('requerimientos.partials.diagrama')
    @include('requerimientos.partials.visor-adjunto')
@endsection

@push('scripts')
    @vite('resources/js/pages/requerimiento-show.js')
@endpush
