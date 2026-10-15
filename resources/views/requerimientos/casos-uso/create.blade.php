@extends('layouts.app')

@section('title', 'Nuevo caso de uso')

@section('content')
    <x-page-header title="Nuevo caso de uso" icon="diagram-3" section="Requerimientos"
                   :subtitle="$requerimiento->codigo.' · '.$requerimiento->titulo"
                   :parent="['url' => route('requerimientos.show', $requerimiento), 'label' => $requerimiento->codigo]" />

    <x-form :action="route('requerimientos.casos-uso.store', $requerimiento)"
            :cancel="route('requerimientos.show', $requerimiento).'#casos-uso'">
        @include('requerimientos.casos-uso._form')
    </x-form>
@endsection
