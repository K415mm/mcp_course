@extends('layouts.guest')

@section('title', 'Verify Email — ' . config('app.name'))

@section('content')
    <div class="login">
        <div class="login-content">
            <!-- Brand -->
            <a href="{{ route('home') }}" class="brand-logo">
                <span class="brand-img"><span>R</span></span>
                <span class="brand-text">RGSOC Academy</span>
            </a>

            <h1 class="text-center h3 fw-bold mb-1">Verify Your Email</h1>
            <p class="text-inverse text-opacity-50 text-center mb-4 fs-13px">
                Thanks for signing up! Before getting started, you need to verify your email address. We've sent a link to
                you.
            </p>

            @if (session('success'))
                <div class="alert alert-success fs-13px py-2 mb-3">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-theme btn-lg d-block w-100 fw-semibold mb-3">
                    <i class="bi bi-envelope-paper me-2"></i>Resend Verification Email
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="text-center">
                @csrf
                <button type="submit" class="btn btn-link text-inverse text-opacity-50 text-decoration-none fs-13px">
                    Log Out
                </button>
            </form>
        </div>
    </div>
@endsection