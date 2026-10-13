@extends('layouts.app')

@section('title', 'Nuevo usuario')

@section('content')
<div class="container-fluid">
    <x-page-header title="Nuevo usuario" icon="person-plus"
                   :parent="['url' => route('admin.usuarios.index'), 'label' => 'Usuarios']" />

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.usuarios.store') }}" autocomplete="off">
                @include('admin.usuarios._form')
            </form>
        </div>
    </div>
</div>
@endsection
