@extends('layouts.app')

@section('title', 'Nuevo rol')

@section('content')
    <x-page-header title="Nuevo rol" icon="plus-circle"
                   :parent="['url' => route('admin.roles.index'), 'label' => 'Roles']" />

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.roles.store') }}" autocomplete="off">
                @include('admin.roles._form')
            </form>
        </div>
    </div>
@endsection
