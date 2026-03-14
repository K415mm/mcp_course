@extends('layouts.guest')

@section('title', 'Sign In — ' . config('app.name'))

@section('content')
    <div class="login">
        <div class="login-content">
            <form method="POST" action="{{ route('login') }}" name="login_form">
                @csrf
                <h1 class="text-center">Sign In</h1>
                <div class="text-inverse text-opacity-50 text-center mb-4">
                    For your protection, please verify your identity.
                </div>

                <!-- Error Alert -->
                @if ($errors->any())
                    <div class="alert alert-danger fs-13px py-2 mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="mb-3">
                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control form-control-lg bg-inverse bg-opacity-5 @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="" required autofocus>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="ms-auto text-inverse text-decoration-none text-opacity-50">Forgot password?</a>
                        @endif
                    </div>
                    <input type="password" name="password" class="form-control form-control-lg bg-inverse bg-opacity-5 @error('password') is-invalid @enderror" value="" placeholder="" required>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="customCheck1" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="customCheck1">Remember me</label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-outline-theme btn-lg d-block w-100 fw-500 mb-3">Sign In</button>
                
                <div class="text-center text-inverse text-opacity-50">
                    Don't have an account yet? <a href="{{ route('register') }}">Sign up</a>.
                </div>
            </form>
        </div>
    </div>
@endsection