@extends('layouts.app')

@section('title', 'Detalle de bitácora')

@section('content')
<div class="container-fluid">
    <x-page-header title="Detalle del registro" icon="journal-text"
                   :parent="['url' => route('admin.bitacora.index').'#registros', 'label' => 'Bitácora']">
        <a href="{{ route('admin.bitacora.index') }}#registros" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver a registros
        </a>
    </x-page-header>

    <div class="card">
        <div class="card-body">
            @include('admin.bitacora.partials.detalle-registro', ['bitacora' => $bitacora])
        </div>
    </div>
</div>
@endsection
