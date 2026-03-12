@extends('layouts.app')

@section('title', 'Access Denied — ' . config('course.title'))

@section('content')
    <div class="row justify-content-center mt-5">
        <div class="col-xl-6 col-lg-8">
            <div class="card text-center py-5" style="border-color: rgba(var(--bs-theme-rgb), 0.3);">
                <div class="card-body">
                    <div class="mb-4">
                        <i class="bi bi-lock-fill text-theme opacity-75" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="h3 fw-bold mb-3">Module Locked</h2>
                    <p class="text-muted fs-16px mb-4 mx-auto" style="max-width: 400px;">
                        Your current access level (<strong class="text-inverse">{{ $userRole }}</strong>)
                        does not include access to <strong>{{ $module['title'] }}</strong>.
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('course.index') }}" class="btn btn-outline-theme btn-lg px-4">
                            <i class="bi bi-arrow-left me-2"></i>Back to Courses
                        </a>
                        <a href="{{ route('profile') }}" class="btn btn-theme btn-lg px-4">
                            <i class="bi bi-star-fill me-2"></i>Upgrade Access
                        </a>
                    </div>
                </div>
                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>
            </div>
        </div>
    </div>
@endsection