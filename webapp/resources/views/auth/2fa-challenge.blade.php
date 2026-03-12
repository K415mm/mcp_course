@extends('layouts.guest')

@section('title', 'Two-Factor Authentication — ' . config('app.name'))

@section('content')
    <div class="login">
        <div class="login-content">
            <!-- Brand -->
            <a href="{{ route('home') }}" class="brand-logo">
                <span class="brand-img"><span>R</span></span>
                <span class="brand-text">RGSOC Academy</span>
            </a>

            <h1 class="text-center h3 fw-bold mb-1">Two-Factor Authentication</h1>
            <p class="text-inverse text-opacity-50 text-center mb-4 fs-13px">
                Please confirm access to your account by entering the authentication code provided by your authenticator
                application.
            </p>

            <!-- Error Alert -->
            @if ($errors->any())
                <div class="alert alert-danger fs-13px py-2 mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('2fa.verify') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Authentication Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" value="{{ old('code') }}"
                        class="form-control form-control-lg bg-inverse bg-opacity-5 @error('code') is-invalid @enderror"
                        placeholder="123456" autocomplete="one-time-code" required autofocus>
                </div>

                <button type="submit" class="btn btn-outline-theme btn-lg d-block w-100 fw-semibold mb-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Verify Sign In
                </button>
                <div class="text-center text-inverse text-opacity-50 fs-13px pt-2">
                    <a href="{{ route('login') }}" class="text-theme">Cancel & Return to Login</a>
                </div>
            </form>
        </div>
    </div>
@endsection