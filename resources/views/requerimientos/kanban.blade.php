@extends('layouts.app')

@section('title', 'Kanban de requerimientos')

@section('content')
    <x-page-header title="Kanban" icon="kanban" section="Requerimientos"
                   subtitle="Arrastre una tarjeta a otra columna para cambiar su estado."
                   :parent="['url' => route('requerimientos.index'), 'label' => 'Requerimientos']">
        <a href="{{ route('requerimientos.calendario') }}" class="btn btn-outline-secondary">
            <i class="bi bi-calendar3"></i> Calendario
        </a>
        @can('create', \App\Models\Requerimiento::class)
            <a href="{{ route('requerimientos.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nuevo requerimiento
            </a>
        @endcan
    </x-page-header>

    <div class="kanban" id="kanban"
         data-url-flujo="{{ route('requerimientos.flujo', ['requerimiento' => '__ID__', 'accion' => '__ACCION__']) }}"
         data-transiciones="{{ json_encode($transiciones) }}">
        @foreach ($estados as $estado)
            @php($tarjetas = $requerimientos->get($estado->value, collect()))
            <section class="kanban__columna" style="--kanban-color: var(--bs-{{ $estado->color() }})" aria-label="{{ $estado->label() }}">
                <header class="kanban__cabecera">
                    <span><i class="bi bi-{{ $estado->icono() }} me-1"></i>{{ $estado->label() }}</span>
                    <span class="badge rounded-pill text-bg-{{ $estado->color() }}" data-contador>{{ $tarjetas->count() }}</span>
                </header>
                @if ($estado === \App\Enums\EstadoRequerimiento::Finalizado)
                    <div class="px-3 small text-body-secondary">Últimos {{ $diasFinalizados }} días</div>
                @endif
                <div class="kanban__lista" data-estado="{{ $estado->value }}" data-etiqueta="{{ $estado->label() }}">
                    @foreach ($tarjetas as $requerimiento)
                        @include('requerimientos.partials.tarjeta-kanban')
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/requerimientos-kanban.js')
@endpush
