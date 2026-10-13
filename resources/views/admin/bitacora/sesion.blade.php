@extends('layouts.app')

@section('title', 'Sesión activa')

@section('content')
<div class="container-fluid">
    <x-page-header title="Timeline de la sesión" icon="clock-history"
                   :parent="['url' => route('admin.bitacora.index').'#sesiones', 'label' => 'Bitácora']">
        <a href="{{ route('admin.bitacora.index') }}#sesiones" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver a sesiones activas
        </a>
    </x-page-header>

    <div class="card">
        <div class="card-body">
            @include('admin.bitacora.partials.timeline-sesion', ['sesion' => $sesion, 'eventos' => $eventos])
        </div>
    </div>
</div>
@endsection
