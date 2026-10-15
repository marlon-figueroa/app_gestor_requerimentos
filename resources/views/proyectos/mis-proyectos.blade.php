@extends('layouts.app')

@section('title', 'Mis proyectos')

@section('content')
    <x-page-header title="Mis proyectos" icon="briefcase" section="Proyectos"
                   subtitle="Proyectos en los que participa con sus requerimientos.">
        @can('create', \App\Models\Requerimiento::class)
            <a href="{{ route('requerimientos.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nuevo requerimiento
            </a>
        @endcan
    </x-page-header>

    <div class="card">
        <div class="card-body p-0">
            @if ($proyectos->isEmpty())
                <p class="text-body-secondary p-4 mb-0">Aún no tiene requerimientos asociados a ningún proyecto.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Responsable</th>
                                <th>Requerimientos</th>
                                <th>Finalización</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($proyectos as $proyecto)
                                <tr>
                                    <td>
                                        @can('view', $proyecto)
                                            @include('proyectos.partials.codigo')
                                        @else
                                            {{ $proyecto->codigo }}
                                        @endcan
                                    </td>
                                    <td>{{ $proyecto->nombre }}</td>
                                    <td>@include('proyectos.partials.estado', ['estado' => $proyecto->estado])</td>
                                    <td>{{ $proyecto->responsable?->name }}</td>
                                    <td>
                                        {{ $proyecto->requerimientos_count - $proyecto->requerimientos_pendientes_count }}/{{ $proyecto->requerimientos_count }} finalizados
                                    </td>
                                    <td class="text-nowrap">@include('proyectos.partials.fecha-finalizacion')</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
