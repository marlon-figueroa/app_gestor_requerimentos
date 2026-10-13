@extends('layouts.guest')

@section('title', __('Login'))

@section('content')
<div class="container my-auto">
    <div class="row justify-content-center">
        <div class="col-sm-10 col-md-7 col-lg-5 col-xl-4">
            <div class="card login-card">
                <div class="card-body px-4 pb-4 pt-0">
                    <div class="text-center mb-4">
                        <img src="{{ asset('image/user_image.png') }}" alt="" class="login-avatar" width="96" height="96">
                        <h1 class="h4 mt-3 mb-1">{{ __('Login') }}</h1>
                        <p class="text-body-secondary small mb-0">{{ config('app.name') }}</p>
                    </div>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('Email Address') }}</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('Password') }}</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label small" for="remember">
                                    {{ __('Remember Me') }}
                                </label>
                            </div>

                            @if (Route::has('password.request'))
                                <a class="small" href="{{ route('password.request') }}">
                                    {{ __('Forgot Your Password?') }}
                                </a>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right me-1"></i> {{ __('Login') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
