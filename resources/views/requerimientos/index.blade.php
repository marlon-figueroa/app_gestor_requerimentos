@extends('layouts.app')

@php
    $titulo = $bandeja ? 'Bandeja de revisión' : 'Requerimientos';
@endphp

@section('title', $titulo)

@section('content')
    <x-page-header :title="$titulo" :icon="$bandeja ? 'inbox' : 'clipboard-check'" section="Requerimientos"
                   :subtitle="$bandeja ? 'Requerimientos enviados a revisión que aún no tienen gestor.' : 'Solicitudes, revisión y seguimiento de requerimientos.'">
        <a href="{{ route('requerimientos.kanban') }}" class="btn btn-outline-secondary">
            <i class="bi bi-kanban"></i> Kanban
        </a>
        <a href="{{ route('requerimientos.calendario') }}" class="btn btn-outline-secondary">
            <i class="bi bi-calendar3"></i> Calendario
        </a>
        @can('create', \App\Models\Requerimiento::class)
            <a href="{{ route('requerimientos.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nuevo requerimiento
            </a>
        @endcan
    </x-page-header>

    <x-filters id="filtros-requerimientos" target="tabla-requerimientos">
        @if ($bandeja)
            <input type="hidden" name="bandeja" value="1">
        @else
            <x-form.select name="estado" label="Estado" :options="$estados" placeholder="Todos los estados" allow-clear col="col-md-3" />
        @endif
        <x-form.input name="fecha_desde" label="Vence desde" type="date" col="col-md-3" />
        <x-form.input name="fecha_hasta" label="Vence hasta" type="date" col="col-md-3" />
        <x-form.checkbox name="vencidos" label="Solo vencidos" switch col="col-md-1 pb-2" />
    </x-filters>

    <x-datatable id="tabla-requerimientos" :url="route('requerimientos.data')" filters="filtros-requerimientos"
                 :order="[[6, 'asc']]"
                 :columns="[
                     ['data' => 'codigo', 'label' => 'Código'],
                     ['data' => 'titulo', 'label' => 'Título'],
                     ['data' => 'solicitante', 'name' => 'solicitante.name', 'label' => 'Solicitante'],
                     ['data' => 'gestor', 'name' => 'gestor.name', 'label' => 'Gestor'],
                     ['data' => 'estado', 'label' => 'Estado', 'searchable' => false],
                     ['data' => 'fecha_solicitud', 'label' => 'Solicitud', 'searchable' => false],
                     ['data' => 'fecha_vencimiento', 'label' => 'Vencimiento', 'searchable' => false],
                     ['data' => 'acciones', 'label' => 'Acciones', 'actions' => true],
                 ]" />
@endsection
