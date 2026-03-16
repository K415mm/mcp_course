@extends('layouts.guest')
@section('title', 'Complete Your Registration — RAISEGUARD Academy')

@section('content')
<div class="login-content" style="max-width:480px;">
    {{-- RAISEGUARD Brand --}}
    <div class="text-center mb-4">
        <div class="mb-3" style="display:inline-flex;align-items:center;gap:10px;">
            <div style="width:44px;height:44px;background:linear-gradient(135deg,#00d4ff,#0066cc);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:900;color:#000;">R</div>
            <span style="font-size:18px;font-weight:700;letter-spacing:2px;color:#fff;">RAISEGUARD</span>
        </div>
        <h1 class="h4 fw-bold text-white">You're Invited! 🎓</h1>
        <p class="text-muted fs-13px mt-1">Complete your registration to access RAISEGUARD Academy</p>
    </div>

    {{-- Email badge --}}
    <div class="mb-4 text-center">
        <div class="d-inline-flex align-items-center gap-2 px-3 py-2"
             style="background:rgba(0,212,255,.08);border:1px solid rgba(0,212,255,.2);border-radius:50px;">
            <i class="bi bi-envelope-check text-theme"></i>
            <span class="text-theme fw-semibold fs-13px">{{ $invitation->email }}</span>
        </div>
        <p class="text-muted fs-11px mt-2">This invitation is exclusively for this email address.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger mb-3 fs-13px">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('invite.register', $token) }}">
        @csrf
        <div class="mb-3">
            <label class="form-label text-muted fs-12px">Your Full Name</label>
            <input type="text" name="name" class="form-control form-control-lg bg-dark border-secondary text-inverse"
                   placeholder="Enter your name" value="{{ old('name', $invitation->name ?? '') }}" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label text-muted fs-12px">Password</label>
            <input type="password" name="password" class="form-control form-control-lg bg-dark border-secondary text-inverse"
                   placeholder="Min 8 characters" required>
        </div>
        <div class="mb-4">
            <label class="form-label text-muted fs-12px">Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control form-control-lg bg-dark border-secondary text-inverse"
                   placeholder="Repeat password" required>
        </div>
        <button type="submit" class="btn btn-theme btn-lg w-100 fw-bold">
            <i class="bi bi-rocket-takeoff me-2"></i>Create My Account
        </button>
    </form>

    <div class="mt-4 p-3 rounded" style="background:rgba(251,191,36,.05);border:1px solid rgba(251,191,36,.15);">
        <div class="d-flex align-items-start gap-2">
            <i class="bi bi-gift text-warning mt-1"></i>
            <div class="fs-12px text-muted">
                <strong class="text-warning">Certificate + Gift included!</strong><br>
                Complete all 8 modules and 5 workshops to earn your RAISEGUARD Academy Certificate of Achievement and an exclusive graduate gift.
            </div>
        </div>
    </div>

    @if($invitation->expires_at)
    <p class="text-center text-muted fs-11px mt-3">
        <i class="bi bi-clock me-1"></i>
        Invitation expires {{ $invitation->expires_at->diffForHumans() }}
    </p>
    @endif
</div>
@endsection
