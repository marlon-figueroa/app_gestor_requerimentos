@extends('layouts.app')

@section('title', 'Bitácora')

@section('content')
@php
    $pestanas = array_filter([
        'registros' => ['label' => 'Registros', 'icon' => 'list', 'visible' => auth()->user()->can('bitacora.ver')],
        'sesiones' => ['label' => 'Sesiones activas', 'icon' => 'users', 'visible' => auth()->user()->can('bitacora.ver')],
        'monitoreo' => ['label' => 'Monitoreo', 'icon' => 'activity', 'visible' => auth()->user()->can('monitoreo.pulse')],
        'logs' => ['label' => 'Logs', 'icon' => 'file-text', 'visible' => auth()->user()->can('monitoreo.logs')],
    ], fn ($pestana) => $pestana['visible']);
    $activa = array_key_first($pestanas);
@endphp
    <x-page-header
        title="Bitácora"
        icon="journal-text"
        subtitle="Registros de actividad, sesiones activas y herramientas de monitoreo."
    >
        @can('bitacora.exportar')
            <a href="{{ route('admin.bitacora.pdf') }}" id="btn-exportar-pdf" class="btn btn-outline-danger @if ($activa !== 'registros') d-none @endif">
                <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
            </a>
        @endcan
    </x-page-header>

    <ul class="nav nav-tabs mb-3" id="bitacora-tabs" role="tablist">
        @foreach ($pestanas as $clave => $pestana)
            <li class="nav-item" role="presentation">
                <button type="button" id="tab-{{ $clave }}" role="tab"
                        class="nav-link d-flex align-items-center gap-2 @if ($clave === $activa) active @endif"
                        data-bs-toggle="tab" data-bs-target="#pane-{{ $clave }}" data-tab="{{ $clave }}"
                        aria-controls="pane-{{ $clave }}" aria-selected="{{ $clave === $activa ? 'true' : 'false' }}">
                    <i data-feather="{{ $pestana['icon'] }}"></i> {{ $pestana['label'] }}
                </button>
            </li>
        @endforeach
    </ul>

    <div class="tab-content">
        @can('bitacora.ver')
            @include('admin.bitacora.partials.registros')
            @include('admin.bitacora.partials.sesiones')
        @endcan

        @can('monitoreo.pulse')
            @include('admin.bitacora.partials.panel-embebido', [
                'clave' => 'monitoreo',
                'titulo' => 'Laravel Pulse',
                'url' => route('pulse'),
            ])
        @endcan

        @can('monitoreo.logs')
            @include('admin.bitacora.partials.panel-embebido', [
                'clave' => 'logs',
                'titulo' => 'Visor de logs',
                'url' => route('log-viewer.index'),
                'urlEmbebida' => route('log-viewer.index', ['embed' => 1]),
            ])
        @endcan
    </div>

@stack('modals')
@endsection

@push('scripts')
    @vite('resources/js/pages/bitacora.js')
@endpush
