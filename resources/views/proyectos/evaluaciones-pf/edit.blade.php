@extends('layouts.app')

@section('title', 'Editar evaluación PF')

@section('content')
    <x-page-header title="Editar evaluación por puntos de función" icon="calculator" section="Proyectos"
                   :parent="['url' => route('proyectos.evaluaciones-pf.show', [$proyecto, $evaluacion]), 'label' => $evaluacion->nombre]" />

    <x-form :action="route('proyectos.evaluaciones-pf.update', [$proyecto, $evaluacion])" method="put"
            :cancel="route('proyectos.evaluaciones-pf.show', [$proyecto, $evaluacion])">
        @include('proyectos.evaluaciones-pf._form')
    </x-form>
@endsection
