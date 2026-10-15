@extends('layouts.app')

@section('title', 'Calendario de requerimientos')

@section('content')
    <x-page-header title="Calendario" icon="calendar3" section="Requerimientos"
                   subtitle="Fechas de vencimiento de los requerimientos."
                   :parent="['url' => route('requerimientos.index'), 'label' => 'Requerimientos']">
        <a href="{{ route('requerimientos.kanban') }}" class="btn btn-outline-secondary">
            <i class="bi bi-kanban"></i> Kanban
        </a>
    </x-page-header>

    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex flex-wrap gap-2 small">
                @foreach ($estados as $estado)
                    <span class="d-inline-flex align-items-center gap-1">
                        <span class="rounded-circle d-inline-block" style="width: .75rem; height: .75rem; background: {{ $estado->hex() }}"></span>
                        {{ $estado->label() }}
                    </span>
                @endforeach
            </div>
            <div class="d-flex flex-wrap gap-2">
                <div style="min-width: 14rem">
                    <label for="calendario-proyecto" class="visually-hidden">Proyecto</label>
                    <select id="calendario-proyecto" class="form-select form-select-sm">
                        <option value="">Todos los proyectos</option>
                        @foreach ($proyectos as $id => $label)
                            <option value="{{ $id }}" @selected($proyectoId == $id)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width: 14rem">
                    <label for="calendario-estado" class="visually-hidden">Estado</label>
                    <select id="calendario-estado" class="form-select form-select-sm">
                        <option value="">Todos los estados</option>
                        @foreach ($estados as $estado)
                            <option value="{{ $estado->value }}">{{ $estado->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div id="calendario" class="calendario-requerimientos"
                 data-url="{{ route('requerimientos.calendario.eventos') }}"></div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/requerimientos-calendario.js')
@endpush
