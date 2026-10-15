@extends('layouts.app')

@section('title', 'Roles')

@section('content')
    <x-page-header title="Roles" icon="person-badge" subtitle="Perfiles de permisos asignables a usuarios.">
        @can('roles.crear')
            <x-page-action-group label="Alta">
                <x-action-btn variant="create" icon="plus-lg" label="Nuevo rol" :href="route('admin.roles.create')" />
            </x-page-action-group>
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
