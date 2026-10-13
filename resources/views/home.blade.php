@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0"><i class="bi bi-speedometer2 me-2"></i>{{ __('Dashboard') }}</h1>
        <span class="text-muted small">{{ now()->translatedFormat('l, d \d\e F \d\e Y') }}</span>
    </div>

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
</div>
@endsection
