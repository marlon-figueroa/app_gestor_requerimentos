@extends('layouts.app')

@section('title', 'Nuevo proyecto')

@section('content')
    <x-page-header title="Nuevo proyecto" icon="briefcase" section="Proyectos"
                   subtitle="El proyecto queda activo al crearlo y desde ese momento puede recibir requerimientos."
                   :parent="['url' => route('proyectos.index'), 'label' => 'Proyectos']" />

    <x-form :action="route('proyectos.store')" files :cancel="route('proyectos.index')">
        @include('proyectos._form')
    </x-form>
@endsection
