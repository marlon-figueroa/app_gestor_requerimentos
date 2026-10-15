@extends('layouts.app')

@php
    use App\Enums\CaracteristicaGeneralPf;
    use App\Enums\TipoComponentePf;
@endphp

@section('title', $evaluacion->nombre)

@section('content')
    <x-page-header :title="$evaluacion->nombre" icon="calculator" section="Proyectos"
                   :parent="['url' => route('proyectos.evaluaciones-pf.index', $proyecto), 'label' => 'Puntos de función']">
        @can('export', $evaluacion)
            <a href="{{ route('proyectos.evaluaciones-pf.pdf', [$proyecto, $evaluacion]) }}" class="btn btn-outline-danger" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
            </a>
        @endcan
        @can('update', $evaluacion)
            <a href="{{ route('proyectos.evaluaciones-pf.edit', [$proyecto, $evaluacion]) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil-square"></i> Editar
            </a>
        @endcan
        @can('delete', $evaluacion)
            <x-delete-button :url="route('proyectos.evaluaciones-pf.destroy', [$proyecto, $evaluacion])" label="Eliminar"
                             confirm="¿Eliminar esta evaluación por puntos de función?" class="btn btn-outline-danger" />
        @endcan
        <a href="{{ route('proyectos.evaluaciones-pf.index', $proyecto) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Listado
        </a>
    </x-page-header>

    @include('proyectos.evaluaciones-pf.partials.diagnostico')

    <div class="row g-4">
        <div class="col-lg-8">
            @if ($evaluacion->notas)
                <div class="card mb-4">
                    <div class="card-header">Notas</div>
                    <div class="card-body">{!! nl2br(e($evaluacion->notas)) !!}</div>
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-header">Componentes funcionales</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tipo</th>
                                    <th>Nombre</th>
                                    <th class="text-end">DET</th>
                                    <th class="text-end">FTR/RET</th>
                                    <th>Complejidad</th>
                                    <th class="text-end">PF</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($evaluacion->componentes as $componente)
                                    <tr>
                                        <td><span class="badge text-bg-light border">{{ $componente->tipo->abreviatura() }}</span></td>
                                        <td>{{ $componente->nombre }}</td>
                                        <td class="text-end">{{ $componente->det }}</td>
                                        <td class="text-end">{{ $componente->referencias }}</td>
                                        <td>{{ $componente->complejidad->label() }}</td>
                                        <td class="text-end fw-semibold">{{ $componente->puntos }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="5" class="text-end">Total sin ajuste</th>
                                    <th class="text-end">{{ $evaluacion->total_sin_ajuste }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">Desglose por tipo</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tipo</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">PF</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (TipoComponentePf::cases() as $tipo)
                                @php $fila = $resumen[$tipo->value]; @endphp
                                @if ($fila['conteo'] > 0)
                                    <tr>
                                        <td>{{ $tipo->label() }}</td>
                                        <td class="text-end">{{ $fila['conteo'] }}</td>
                                        <td class="text-end">{{ $fila['puntos'] }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">Totales IFPUG</div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-7">PF sin ajuste</dt>
                        <dd class="col-5 text-end fw-semibold">{{ $evaluacion->total_sin_ajuste }}</dd>
                        <dt class="col-7">Suma de influencia</dt>
                        <dd class="col-5 text-end">{{ $evaluacion->suma_influencia }}</dd>
                        <dt class="col-7">Factor de ajuste</dt>
                        <dd class="col-5 text-end">{{ number_format($evaluacion->factor_ajuste, 2, ',', '.') }}</dd>
                        <dt class="col-7">PF ajustados</dt>
                        <dd class="col-5 text-end fs-5 fw-bold text-primary">{{ number_format($evaluacion->total_ajustado, 2, ',', '.') }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">Características generales</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush small">
                        @foreach (CaracteristicaGeneralPf::ordenadas() as $caracteristica)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $caracteristica->label() }}</span>
                                <span class="fw-semibold">{{ $evaluacion->caracteristicas[$caracteristica->value] ?? '—' }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Datos</div>
                <div class="card-body small">
                    <dl class="row mb-0">
                        <dt class="col-5">Proyecto</dt>
                        <dd class="col-7">{{ $proyecto->codigo }}</dd>
                        <dt class="col-5">Autor</dt>
                        <dd class="col-7">{{ $evaluacion->creador?->name ?? '—' }}</dd>
                        <dt class="col-5">Creada</dt>
                        <dd class="col-7">{{ $evaluacion->created_at?->format('d/m/Y H:i') }}</dd>
                        <dt class="col-5">Actualizada</dt>
                        <dd class="col-7">{{ $evaluacion->updated_at?->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
