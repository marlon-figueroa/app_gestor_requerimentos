@extends('layouts.app')

@section('title', 'Sesión activa')

@section('content')
    <x-page-header
        title="Timeline de la sesión"
        icon="clock-history"
        :subtitle="$sesion->user ? $sesion->user->name : 'Sesión sin usuario'"
        :parent="['url' => route('admin.bitacora.index').'#sesiones', 'label' => 'Bitácora']"
    >
        <a href="{{ route('admin.bitacora.index') }}#sesiones" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver a sesiones activas
        </a>
    </x-page-header>

    <div class="card">
        <div class="card-body">
            @include('admin.bitacora.partials.timeline-sesion', ['sesion' => $sesion, 'eventos' => $eventos])
        </div>
    </div>
@endsection
