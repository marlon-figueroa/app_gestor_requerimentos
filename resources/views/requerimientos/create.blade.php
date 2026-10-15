@extends('layouts.app')

@section('title', 'Nuevo requerimiento')

@section('content')
    <x-page-header title="Nuevo requerimiento" icon="clipboard-plus" section="Requerimientos"
                   subtitle="Se guarda como borrador; envíelo a revisión cuando esté completo."
                   :parent="['url' => route('requerimientos.index'), 'label' => 'Requerimientos']" />

    <x-form :action="route('requerimientos.store')" files :cancel="route('requerimientos.index')">
        @include('requerimientos._form')
    </x-form>
@endsection
