@extends('layouts.app')

@section('title', 'Nuevo usuario')

@section('content')
    <x-page-header title="Nuevo usuario" icon="person-plus"
                   :parent="['url' => route('admin.usuarios.index'), 'label' => 'Usuarios']" />

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.usuarios.store') }}" enctype="multipart/form-data" autocomplete="off">
                @include('admin.usuarios._form')
            </form>
        </div>
    </div>
@endsection
