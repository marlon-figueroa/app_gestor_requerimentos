@extends('layouts.app')

@section('title', 'Proyectos')

@section('content')
    <x-page-header title="Proyectos" icon="briefcase" section="Proyectos"
                   subtitle="Proyectos activos y cerrados, con su responsable y los requerimientos que ejecutan.">
        @can('create', \App\Models\Proyecto::class)
            <a href="{{ route('proyectos.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nuevo proyecto
            </a>
        @endcan
    </x-page-header>

    <x-filters id="filtros-proyectos" target="tabla-proyectos">
        <x-form.select name="estado" label="Estado" :options="$estados" placeholder="Todos los estados" allow-clear col="col-md-3" />
        <x-form.select name="responsable_id" label="Responsable" :options="$responsables" placeholder="Todos los responsables" allow-clear col="col-md-4" />
    </x-filters>

    <x-datatable id="tabla-proyectos" :url="route('proyectos.data')" filters="filtros-proyectos"
                 :order="[[5, 'asc']]"
                 :columns="[
                     ['data' => 'codigo', 'label' => 'Código'],
                     ['data' => 'nombre', 'label' => 'Nombre'],
                     ['data' => 'responsable', 'name' => 'responsable.name', 'label' => 'Responsable'],
                     ['data' => 'avance', 'label' => 'Requerimientos', 'orderable' => false, 'searchable' => false],
                     ['data' => 'estado', 'label' => 'Estado', 'searchable' => false],
                     ['data' => 'fecha_finalizacion', 'label' => 'Finalización', 'searchable' => false],
                     ['data' => 'acciones', 'label' => 'Acciones', 'actions' => true],
                 ]" />
@endsection
