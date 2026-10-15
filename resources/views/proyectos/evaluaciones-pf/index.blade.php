@extends('layouts.app')

@section('title', 'Puntos de función · '.$proyecto->codigo)

@section('content')
    <x-page-header :title="'Puntos de función · '.$proyecto->codigo" icon="calculator" section="Proyectos"
                   subtitle="Evaluaciones IFPUG del proyecto."
                   :parent="['url' => route('proyectos.show', $proyecto), 'label' => $proyecto->codigo]">
        @can('create', [\App\Models\ProyectoEvaluacionPf::class, $proyecto])
            <a href="{{ route('proyectos.evaluaciones-pf.create', $proyecto) }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nueva evaluación
            </a>
        @endcan
        <a href="{{ route('proyectos.show', $proyecto) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver al proyecto
        </a>
    </x-page-header>

    <div class="card">
        <div class="card-body p-0">
            @if ($evaluaciones->isEmpty())
                <p class="text-body-secondary p-4 mb-0">Aún no hay evaluaciones por puntos de función para este proyecto.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th class="text-end">PF sin ajuste</th>
                                <th class="text-end">PF ajustados</th>
                                <th>Viabilidad</th>
                                <th class="text-end">Tiempo est.</th>
                                <th>Autor</th>
                                <th>Fecha</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($evaluaciones as $evaluacion)
                                <tr>
                                    <td>
                                        <a href="{{ route('proyectos.evaluaciones-pf.show', [$proyecto, $evaluacion]) }}" class="text-decoration-none fw-semibold">
                                            {{ $evaluacion->nombre }}
                                        </a>
                                    </td>
                                    <td class="text-end">{{ $evaluacion->total_sin_ajuste }}</td>
                                    <td class="text-end">{{ number_format($evaluacion->total_ajustado, 2, ',', '.') }}</td>
                                    @php
                                        $d = $diagnosticos[$evaluacion->id];
                                        $viabilidadFila = $d['viabilidad'];
                                        $viabilidadFila = $viabilidadFila instanceof \App\Enums\ViabilidadPf
                                            ? $viabilidadFila
                                            : \App\Enums\ViabilidadPf::from($viabilidadFila);
                                    @endphp
                                    <td>
                                        <x-viabilidad-pf-badge :viabilidad="$viabilidadFila" :short="true" />
                                        @if ($evaluacion->es_baseline)
                                            <span class="badge text-bg-success ms-1">Baseline</span>
                                        @endif
                                    </td>
                                    <td class="text-end small">{{ $d['tiempo']['dias_laborables'] }} días lab.</td>
                                    <td>{{ $evaluacion->creador?->name ?? '—' }}</td>
                                    <td>{{ $evaluacion->created_at?->format('d/m/Y H:i') }}</td>
                                    <td class="text-end text-nowrap">
                                        <a href="{{ route('proyectos.evaluaciones-pf.show', [$proyecto, $evaluacion]) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                                        @can('update', $evaluacion)
                                            <a href="{{ route('proyectos.evaluaciones-pf.edit', [$proyecto, $evaluacion]) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
