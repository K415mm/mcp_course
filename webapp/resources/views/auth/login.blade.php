@extends('layouts.guest')

@section('title', 'Sign In — ' . config('app.name'))

@section('content')
    <div class="login">
        <div class="login-content">
            <!-- Brand -->
            <a href="{{ route('login') }}" class="brand-logo">
                <span class="brand-img"><span>R</span></span>
                <span class="brand-text">RGSOC Academy</span>
            </a>

            <h1 class="text-center h3 fw-bold mb-1">Sign In</h1>
            <p class="text-inverse text-opacity-50 text-center mb-4 fs-13px">
                For your protection, please verify your identity.
            </p>

            <!-- Error Alert -->
            @if ($errors->any())
                <div class="alert alert-danger fs-13px py-2 mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="form-control form-control-lg bg-inverse bg-opacity-5 @error('email') is-invalid @enderror"
                        placeholder="your@email.com" required autofocus>
                </div>
                <div class="mb-3">
                    <div class="d-flex">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                    </div>
                    <input type="password" name="password"
                        class="form-control form-control-lg bg-inverse bg-opacity-5 @error('password') is-invalid @enderror"
                        placeholder="••••••••" required>
                </div>
                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label text-inverse text-opacity-60" for="remember">Remember me</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-outline-theme btn-lg d-block w-100 fw-semibold mb-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </button>
                <div class="text-center text-inverse text-opacity-50 fs-13px">
                    Don't have an account? <a href="{{ route('register') }}" class="text-theme">Sign up</a>
                </div>
            </form>
        </div>
    </div>
@endsection