@extends('layouts.app')

@section('title', 'Editar rol')

@section('content')
<div class="container-fluid">
    <x-page-header title="Editar rol" icon="pencil-square"
                   :parent="['url' => route('admin.roles.index'), 'label' => 'Roles']" />

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.roles.update', $role) }}" autocomplete="off">
                @method('PUT')
                @include('admin.roles._form')
            </form>
        </div>
    </div>
</div>
@endsection
