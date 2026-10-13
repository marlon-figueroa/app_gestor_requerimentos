@extends('layouts.app')

@section('title', 'Editar usuario')

@section('content')
<div class="container-fluid">
    <x-page-header title="Editar usuario" icon="pencil-square"
                   :parent="['url' => route('admin.usuarios.index'), 'label' => 'Usuarios']" />

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.usuarios.update', $user) }}" autocomplete="off">
                @method('PUT')
                @include('admin.usuarios._form')
            </form>
        </div>
    </div>
</div>
@endsection
