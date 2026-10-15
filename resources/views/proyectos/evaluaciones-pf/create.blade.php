@extends('layouts.app')

@section('title', 'Nueva evaluación PF')

@section('content')
    <x-page-header title="Nueva evaluación por puntos de función" icon="calculator" section="Proyectos"
                   :parent="['url' => route('proyectos.evaluaciones-pf.index', $proyecto), 'label' => 'Puntos de función']" />

    <x-form :action="route('proyectos.evaluaciones-pf.store', $proyecto)" :cancel="route('proyectos.evaluaciones-pf.index', $proyecto)">
        @include('proyectos.evaluaciones-pf._form')
    </x-form>
@endsection
