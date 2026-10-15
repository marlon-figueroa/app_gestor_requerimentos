@extends('layouts.app')

@section('title', 'Kanban de requerimientos')

@section('content')
    <x-page-header title="Kanban" icon="kanban" section="Requerimientos"
                   subtitle="Arrastre una tarjeta a otra columna para cambiar su estado."
                   :parent="['url' => route('requerimientos.index'), 'label' => 'Requerimientos']">
        <x-page-action-group label="Vistas">
            <x-action-btn variant="nav" icon="list-ul" label="Listado" :href="route('requerimientos.index')" />
            <x-action-btn variant="nav" icon="calendar3" label="Calendario" :href="route('requerimientos.calendario')" />
        </x-page-action-group>
        @can('create', \App\Models\Requerimiento::class)
            <x-page-action-group label="Alta">
                <x-action-btn variant="create" icon="plus-lg" label="Nuevo requerimiento" :href="route('requerimientos.create')" />
            </x-page-action-group>
        @endcan
    </x-page-header>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('requerimientos.kanban') }}" class="row g-3 align-items-end">
                <x-form.select name="proyecto_id" label="Proyecto" :options="$proyectos" :selected="$proyectoId" placeholder="Todos" allow-clear col="col-md-4" />
                <x-form.select name="prioridad" label="Prioridad" :options="$prioridades" :selected="$prioridad" placeholder="Todas" allow-clear col="col-md-3" />
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search"></i></button>
                    <a href="{{ route('requerimientos.kanban') }}" class="btn btn-outline-secondary flex-fill"><i class="bi bi-x-circle"></i></a>
                </div>
            </form>
        </div>
    </div>

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
