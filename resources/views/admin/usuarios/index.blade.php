@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
    <x-page-header title="Usuarios" icon="people" subtitle="Administración de cuentas y accesos.">
        @can('usuarios.exportar')
            <a href="{{ route('admin.usuarios.pdf') }}" id="btn-exportar-pdf" class="btn btn-outline-danger">
                <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
            </a>
        @endcan
        @can('usuarios.crear')
            <a href="{{ route('admin.usuarios.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nuevo usuario
            </a>
        @endcan
    </x-page-header>

    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-funnel me-1"></i> Filtros</div>
        <div class="card-body">
            <form id="filtros-usuarios" class="row g-3 align-items-end" autocomplete="off">
                <div class="col-md-4">
                    <label for="rol" class="form-label">Rol</label>
                    <select id="rol" name="rol" class="form-select select2" data-placeholder="Todos los roles" data-allow-clear="true">
                        <option value=""></option>
                        @foreach ($roles as $rol)
                            <option value="{{ $rol }}">{{ $rol }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="fecha_desde" class="form-label">Alta desde</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                        <input type="text" id="fecha_desde" name="fecha_desde" class="form-control datetimepicker" placeholder="dd/mm/aaaa">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="fecha_hasta" class="form-label">Alta hasta</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                        <input type="text" id="fecha_hasta" name="fecha_hasta" class="form-control datetimepicker" placeholder="dd/mm/aaaa">
                    </div>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill" title="Filtrar">
                        <i class="bi bi-search"></i>
                    </button>
                    <button type="reset" class="btn btn-outline-secondary flex-fill" title="Limpiar">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table id="tabla-usuarios" class="table table-hover align-middle w-100" data-url="{{ route('admin.usuarios.data') }}">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo electrónico</th>
                        <th>Roles</th>
                        <th>Fecha de alta</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/usuarios.js')
@endpush
