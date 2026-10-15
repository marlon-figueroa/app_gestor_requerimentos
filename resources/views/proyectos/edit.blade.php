@extends('layouts.app')

@section('title', 'Editar '.$proyecto->codigo)

@section('content')
    <x-page-header :title="'Editar '.$proyecto->codigo" icon="pencil-square" section="Proyectos"
                   :parent="['url' => route('proyectos.index'), 'label' => 'Proyectos']" />

    <x-form :action="route('proyectos.update', $proyecto)" method="PUT" files
            :cancel="route('proyectos.show', $proyecto)">
        @include('proyectos._form')
    </x-form>
@endsection
