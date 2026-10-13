@extends('layouts.base')

@section('body-class', 'guest-layout')

@section('body')
    <nav class="navbar navbar-expand navbar-dark bg-navy shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <i class="bi bi-clipboard-check me-1"></i>
                {{ config('app.name', 'Laravel') }}
            </a>

            <ul class="navbar-nav ms-auto">
                @include('partials.theme-toggle')

                @if (Route::has('login') && ! request()->routeIs('login'))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right me-1"></i> <span class="d-none d-sm-inline">{{ __('Login') }}</span>
                        </a>
                    </li>
                @endif

                @if (Route::has('register') && ! request()->routeIs('register'))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">
                            <i class="bi bi-person-plus me-1"></i> <span class="d-none d-sm-inline">{{ __('Register') }}</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </nav>

    <main class="guest-content">
        <div class="container-fluid">
            @include('partials.alerts')

            @yield('content')
        </div>
    </main>

    @include('layouts.partials.footer')
@endsection
