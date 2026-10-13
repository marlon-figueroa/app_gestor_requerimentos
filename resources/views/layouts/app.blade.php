@extends('layouts.base')

@section('head')
    {{-- Aplica el estado del sidebar antes de pintar para evitar saltos en escritorio. --}}
    <script>
        try {
            if (localStorage.getItem('sidebar') === 'collapsed') {
                document.documentElement.classList.add('sidebar-collapsed');
            }
        } catch (e) {}
    </script>
@endsection

@section('body-class', 'app-layout')

@section('body')
    <div class="app-wrapper">
        @include('layouts.partials.sidebar')

        <div class="app-main">
            @include('layouts.partials.topbar')

            <main class="app-content">
                <div class="container-fluid">
                    @include('partials.alerts')

                    @yield('content')
                </div>
            </main>

            @include('layouts.partials.footer')
        </div>
    </div>
@endsection
