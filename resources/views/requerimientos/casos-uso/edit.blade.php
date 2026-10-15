@extends('layouts.app')

@section('title', 'Editar '.$casoUso->codigo)

@section('content')
    <x-page-header :title="'Editar '.$casoUso->codigo" icon="diagram-3" section="Requerimientos"
                   :subtitle="$requerimiento->codigo.' · '.$requerimiento->titulo"
                   :parent="['url' => route('requerimientos.show', $requerimiento), 'label' => $requerimiento->codigo]" />

    <x-form :action="route('requerimientos.casos-uso.update', [$requerimiento, $casoUso])" method="PUT"
            :cancel="route('requerimientos.show', $requerimiento).'#casos-uso'">
        @include('requerimientos.casos-uso._form')
    </x-form>
@endsection
