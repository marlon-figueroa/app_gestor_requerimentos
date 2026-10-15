@extends('layouts.app')

@section('title', 'Editar '.$requerimiento->codigo)

@section('content')
    <x-page-header :title="'Editar '.$requerimiento->codigo" icon="pencil-square" section="Requerimientos"
                   :parent="['url' => route('requerimientos.index'), 'label' => 'Requerimientos']" />

    <x-form :action="route('requerimientos.update', $requerimiento)" method="PUT" files
            :cancel="route('requerimientos.show', $requerimiento)">
        @include('requerimientos._form')
    </x-form>
@endsection
