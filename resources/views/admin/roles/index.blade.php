@extends('layouts.app')

@section('title', 'Roles')

@section('content')
    <x-page-header title="Roles" icon="person-badge" subtitle="Perfiles de permisos asignables a usuarios.">
        @can('roles.crear')
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nuevo rol
            </a>
        @endcan
    </x-page-header>

    <div class="card">
        <div class="card-body">
            <table id="tabla-roles" class="table table-hover align-middle w-100" data-url="{{ route('admin.roles.data') }}">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Permisos</th>
                        <th>Usuarios</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/roles.js')
@endpush
