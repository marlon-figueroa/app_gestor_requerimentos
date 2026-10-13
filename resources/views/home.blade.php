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
@endsection
