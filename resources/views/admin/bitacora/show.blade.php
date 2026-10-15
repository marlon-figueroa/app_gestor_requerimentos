@extends('layouts.app')

@section('title', 'Detalle de bitácora')

@section('content')
    <x-page-header
        title="Detalle del registro"
        icon="journal-text"
        :subtitle="$bitacora->accion"
        :parent="['url' => route('admin.bitacora.index').'#registros', 'label' => 'Bitácora']"
    >
        <x-page-action-group label="Navegación">
            <x-action-btn variant="nav" icon="arrow-left" label="Volver a registros" :href="route('admin.bitacora.index').'#registros'" />
        </x-page-action-group>
    </x-page-header>

    <div class="card">
        <div class="card-body">
            @include('admin.bitacora.partials.detalle-registro', ['bitacora' => $bitacora])
        </div>
    </div>
@endsection
