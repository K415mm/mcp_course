@extends('layouts.guest')

@section('title', 'Create Account — ' . config('app.name'))

@section('content')
    <div class="register">
        <div class="register-content">
            <form method="POST" action="{{ route('register') }}" name="register_form">
                @csrf
                <h1 class="text-center">Sign Up</h1>
                <p class="text-inverse text-opacity-50 text-center">One Admin ID is all you need to access all the Admin services.</p>

                @if ($errors->any())
                    <div class="alert alert-danger fs-13px py-2 mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control form-control-lg bg-inverse bg-opacity-5 @error('name') is-invalid @enderror" placeholder="e.g John Smith" value="{{ old('name') }}" required autofocus>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control form-control-lg bg-inverse bg-opacity-5 @error('email') is-invalid @enderror" placeholder="username@address.com" value="{{ old('email') }}" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control form-control-lg bg-inverse bg-opacity-5 @error('password') is-invalid @enderror" value="" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" name="password_confirmation" class="form-control form-control-lg bg-inverse bg-opacity-5" value="" required>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="customCheck1" required>
                        <label class="form-check-label" for="customCheck1">I have read and agree to the <a href="#">Terms of Use</a> and <a href="#">Privacy Policy</a>.</label>
                    </div>
                </div>
                
                <div class="mb-3">
                    <button type="submit" class="btn btn-outline-theme btn-lg d-block w-100">Sign Up</button>
                </div>
                
                <div class="text-inverse text-opacity-50 text-center">
                    Already have an Admin ID? <a href="{{ route('login') }}">Sign In</a>
                </div>
            </form>
        </div>
    </div>
@endsection