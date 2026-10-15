@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
    <x-page-header
        :title="__('Dashboard')"
        icon="speedometer2"
        :subtitle="now()->translatedFormat('l, d \d\e F \d\e Y')"
        :section="null"
    />

    <p class="lead">Bienvenido(a), <strong>{{ Auth::user()->name }}</strong>.</p>

    <div class="row g-4">
        @foreach ($stats as $stat)
            <div class="col-md-4">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted text-uppercase small">{{ $stat['label'] }}</div>
                            <div class="display-6 fw-bold">{{ $stat['total'] }}</div>
                        </div>
                        <i class="bi bi-{{ $stat['icon'] }} stat-icon text-{{ $stat['color'] }}"></i>
                    </div>
                    @if ($stat['route'])
                        <div class="card-footer border-0">
                            <a href="{{ $stat['route'] }}" class="small text-decoration-none">
                                Ver detalle <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="card mt-4" id="dashboard-requerimientos">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <span><i class="bi bi-bar-chart-line me-1"></i> Evolución de requerimientos</span>
            <div class="d-flex flex-wrap align-items-center gap-2">
                @can('viewAny', \App\Models\Requerimiento::class)
                    <a href="{{ route('requerimientos.index') }}" class="btn btn-sm btn-outline-secondary">Ver listado</a>
                @endcan
                <div class="btn-group btn-group-sm" role="group" aria-label="Periodo de la evolución">
                    <button type="button" class="btn btn-outline-primary active" data-periodo="semanal" aria-pressed="true">Semanal</button>
                    <button type="button" class="btn btn-outline-primary" data-periodo="mensual" aria-pressed="false">Mensual</button>
                    <button type="button" class="btn btn-outline-primary" data-periodo="anual" aria-pressed="false">Anual</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <p class="text-body-secondary small mb-3" id="dashboard-requerimientos-resumen"></p>
            <div class="row g-4">
                <div class="col-lg-7">
                    <h2 class="h6 text-body-secondary">Creados</h2>
                    <div class="dashboard-grafica">
                        <canvas id="grafica-barras" aria-label="Barras de requerimientos creados"></canvas>
                    </div>
                </div>
                <div class="col-lg-5">
                    <h2 class="h6 text-body-secondary">Por estado</h2>
                    <div class="dashboard-grafica">
                        <canvas id="grafica-pastel" aria-label="Pastel de requerimientos por estado"></canvas>
                    </div>
                    <p class="text-center text-body-secondary small d-none mb-0" id="grafica-pastel-vacio">No hay requerimientos en este periodo.</p>
                </div>
            </div>
        </div>
    </div>

    <script type="application/json" id="dashboard-requerimientos-datos">@json($graficas)</script>
@endsection

@push('scripts')
    @vite('resources/js/pages/home.js')
@endpush
