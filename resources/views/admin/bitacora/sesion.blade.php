@extends('layouts.app')

@section('title', 'Sesión activa')

@section('content')
    <x-page-header
        title="Timeline de la sesión"
        icon="clock-history"
        :subtitle="$sesion->user ? $sesion->user->name : 'Sesión sin usuario'"
        :parent="['url' => route('admin.bitacora.index').'#sesiones', 'label' => 'Bitácora']"
    >
        <x-page-action-group label="Navegación">
            <x-action-btn variant="nav" icon="arrow-left" label="Volver a sesiones activas" :href="route('admin.bitacora.index').'#sesiones'" />
        </x-page-action-group>
    </x-page-header>

    <div class="card">
        <div class="card-body">
            @include('admin.bitacora.partials.timeline-sesion', ['sesion' => $sesion, 'eventos' => $eventos])
        </div>
    </div>
@endsection
