@extends('layouts.guest')

@section('title', 'Create Account — ' . config('app.name'))

@section('content')
    <div class="register">
        <div class="register-content">
            <!-- Brand -->
            <a href="{{ route('login') }}" class="brand-logo">
                <span class="brand-img"><span>R</span></span>
                <span class="brand-text">RGSOC Academy</span>
            </a>

            <h1 class="text-center h3 fw-bold mb-1">Create Account</h1>
            <p class="text-inverse text-opacity-50 text-center mb-4 fs-13px">
                Join RGSOC Academy to access all course modules and workshops.
            </p>

            @if ($errors->any())
                <div class="alert alert-danger fs-13px py-2 mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="form-control form-control-lg bg-inverse bg-opacity-5 @error('name') is-invalid @enderror"
                        placeholder="John Smith" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="form-control form-control-lg bg-inverse bg-opacity-5 @error('email') is-invalid @enderror"
                        placeholder="your@email.com" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" name="password"
                        class="form-control form-control-lg bg-inverse bg-opacity-5 @error('password') is-invalid @enderror"
                        placeholder="Min. 8 characters" required>
                </div>
                <div class="mb-4">
                    <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" name="password_confirmation"
                        class="form-control form-control-lg bg-inverse bg-opacity-5" placeholder="Repeat password" required>
                </div>
                <button type="submit" class="btn btn-outline-theme btn-lg d-block w-100 fw-semibold mb-3">
                    <i class="bi bi-person-plus me-2"></i>Create Account
                </button>
                <div class="text-center text-inverse text-opacity-50 fs-13px">
                    Already have an account? <a href="{{ route('login') }}" class="text-theme">Sign in</a>
                </div>
            </form>
        </div>
    </div>
@endsection