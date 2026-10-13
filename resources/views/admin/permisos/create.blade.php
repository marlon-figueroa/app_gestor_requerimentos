@extends('layouts.app')

@section('title', 'Nuevo permiso')

@section('content')
<div class="container-fluid">
    <x-page-header title="Nuevo permiso" icon="plus-circle"
                   :parent="['url' => route('admin.permisos.index'), 'label' => 'Permisos']" />

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.permisos.store') }}" autocomplete="off">
                @include('admin.permisos._form')
            </form>
        </div>
    </div>
</div>
@endsection
