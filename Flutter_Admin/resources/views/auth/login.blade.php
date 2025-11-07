@extends('layouts.guest')

@section('content')
    <div class="text-center mb-4">
        <h2 class="fw-bold">Login</h2>
        <p class="text-muted">Please sign in to continue</p>
    </div>

    {{-- Session Status --}}
    @if (session('status'))
        <div class="alert alert-success text-center py-2 mb-3">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Email Address --}}
        <div class="mb-3">
            <label for="email" class="form-label fw-semibold">{{ __('Email Address') }}</label>
            <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" required autofocus autocomplete="username">

            @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Password --}}
        <div class="mb-3">
            <label for="password" class="form-label fw-semibold">{{ __('Password') }}</label>
            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                   required autocomplete="current-password">

            @error('password')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Remember Me --}}
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
            <label class="form-check-label" for="remember_me">
                {{ __('Remember Me') }}
            </label>
        </div>

        {{-- Submit + Forgot Password --}}
        <div class="d-flex justify-content-between align-items-center">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-decoration-none small text-primary">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <button type="submit" class="btn btn-primary px-4">
                {{ __('Log in') }}
            </button>
        </div>
    </form>
@endsection
