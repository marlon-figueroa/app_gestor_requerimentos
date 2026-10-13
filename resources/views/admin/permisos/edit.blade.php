@extends('layouts.app')

@section('title', 'Editar permiso')

@section('content')
    <x-page-header title="Editar permiso" icon="pencil-square"
                   :parent="['url' => route('admin.permisos.index'), 'label' => 'Permisos']" />

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.permisos.update', $permission) }}" autocomplete="off">
                @method('PUT')
                @include('admin.permisos._form')
            </form>
        </div>
    </div>
@endsection
