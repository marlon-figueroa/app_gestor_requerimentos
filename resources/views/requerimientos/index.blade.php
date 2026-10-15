@extends('layouts.app')

@php
    $titulo = $bandeja ? 'Bandeja de revisión' : 'Requerimientos';
@endphp

@section('title', $titulo)

@section('content')
    <x-page-header :title="$titulo" :icon="$bandeja ? 'inbox' : 'clipboard-check'" section="Requerimientos"
                   :subtitle="$bandeja ? 'Requerimientos enviados a revisión que aún no tienen gestor.' : 'Solicitudes, revisión y seguimiento de requerimientos.'">
        <x-page-action-group label="Vistas">
            <x-action-btn variant="nav" icon="kanban" label="Kanban" :href="route('requerimientos.kanban')" />
            <x-action-btn variant="nav" icon="calendar3" label="Calendario" :href="route('requerimientos.calendario')" />
            @can('exportAny', \App\Models\Requerimiento::class)
                <x-action-btn variant="export" icon="filetype-csv" label="Exportar CSV" :href="route('requerimientos.export.csv')" id="export-csv-link" />
            @endcan
        </x-page-action-group>
        @can('create', \App\Models\Requerimiento::class)
            <x-page-action-group label="Alta">
                <x-action-btn variant="create" icon="plus-lg" label="Nuevo requerimiento" :href="route('requerimientos.create')" />
            </x-page-action-group>
        @endcan
    </x-page-header>

    <x-filters id="filtros-requerimientos" target="tabla-requerimientos">
        @if ($bandeja)
            <input type="hidden" name="bandeja" value="1">
        @else
            <x-form.select name="estado" label="Estado" :options="$estados" placeholder="Todos los estados" allow-clear col="col-md-2" />
        @endif
        <x-form.select name="proyecto_id" label="Proyecto" :options="$proyectos" placeholder="Todos los proyectos" allow-clear col="col-md-3" />
        @unless ($bandeja)
            <x-form.select name="prioridad" label="Prioridad" :options="$prioridades ?? []" placeholder="Todas" allow-clear col="col-md-2" />
        @endunless
        <x-form.input name="fecha_desde" label="Vence desde" type="date" col="col-md-2" />
        <x-form.input name="fecha_hasta" label="Vence hasta" type="date" col="col-md-2" />
        <x-form.checkbox name="vencidos" label="Solo vencidos" switch col="col-md-1 pb-2" />
    </x-filters>

    <x-datatable id="tabla-requerimientos" :url="route('requerimientos.data')" filters="filtros-requerimientos"
                 :order="[[8, 'asc']]"
                 :columns="[
                     ['data' => 'codigo', 'label' => 'Código'],
                     ['data' => 'titulo', 'label' => 'Título'],
                     ['data' => 'proyecto', 'name' => 'proyecto.nombre', 'label' => 'Proyecto'],
                     ['data' => 'solicitante', 'name' => 'solicitante.name', 'label' => 'Solicitante'],
                     ['data' => 'gestor', 'name' => 'gestor.name', 'label' => 'Gestor'],
                     ['data' => 'prioridad', 'label' => 'Prioridad', 'searchable' => false],
                     ['data' => 'estado', 'label' => 'Estado', 'searchable' => false],
                     ['data' => 'fecha_solicitud', 'label' => 'Solicitud', 'searchable' => false],
                     ['data' => 'fecha_vencimiento', 'label' => 'Vencimiento', 'searchable' => false],
                     ['data' => 'acciones', 'label' => 'Acciones', 'actions' => true],
                 ]" />
@endsection
