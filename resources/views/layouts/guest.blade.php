@extends('layouts.base')

@section('body-class', 'guest-layout')

@section('body')
    <nav class="navbar navbar-expand app-topbar shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
                <span class="app-sidebar-brand-mark" aria-hidden="true">
                    <i data-feather="clipboard"></i>
                </span>
                <span class="fw-bold">{{ config('app.name', 'Laravel') }}</span>
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
        <div class="container-fluid d-flex flex-column flex-grow-1">
            @include('partials.alerts')

            @yield('content')
        </div>
    </main>

    @include('layouts.partials.footer')
@endsection
