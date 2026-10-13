@extends('layouts.app')

@section('title', 'Permisos')

@section('content')
    <x-page-header title="Permisos" icon="key" subtitle="Capacidades individuales del sistema.">
        @can('permisos.crear')
            <a href="{{ route('admin.permisos.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nuevo permiso
            </a>
        @endcan
    </x-page-header>

    <div class="card">
        <div class="card-body">
            <table id="tabla-permisos" class="table table-hover align-middle w-100" data-url="{{ route('admin.permisos.data') }}">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Roles</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/permisos.js')
@endpush
