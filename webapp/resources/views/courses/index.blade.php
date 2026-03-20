@extends('layouts.app')

@section('title', 'My Courses')

@section('content')
<div class="d-flex align-items-center mb-4">
    <div>
        <h1 class="page-header mb-0">My Enrolled Courses</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Courses</li>
        </ol>
    </div>
</div>

<div class="row gx-4">
    @forelse($enrolledCourses as $course)
        <div class="col-xl-4 col-lg-6 mb-4">
            <a href="{{ route('courses.show', $course['slug']) }}" class="text-decoration-none">
                <div class="card h-100 module-card border-0 bg-dark" style="background: linear-gradient(145deg, rgba(23,24,25,1) 0%, rgba(30,32,34,1) 100%);">
                    <div class="card-body p-4 position-relative overflow-hidden">
                        
                        {{-- Decorative background icon --}}
                        <div class="position-absolute opacity-10" style="bottom: -20px; right: -20px; font-size: 8rem; color: var(--bs-theme);">
                            <i class="bi bi-journal-code"></i>
                        </div>

                        <div class="d-flex align-items-center mb-3 position-relative z-1">
                            <div class="bg-theme text-dark rounded-circle d-flex align-items-center justify-content-center me-3 shadow" style="width: 48px; height: 48px; font-size: 1.5rem;">
                                <i class="bi bi-journal-bookmark-fill"></i>
                            </div>
                            <h4 class="mb-0 text-white shadow-sm">{{ $course['title'] }}</h4>
                        </div>
                        
                        <p class="text-white text-opacity-75 mb-4 position-relative z-1" style="min-height: 48px;">
                            {{ $course['description'] ?? 'This course contains modules and hands-on workshops.' }}
                        </p>
                        
                        <div class="d-flex align-items-center justify-content-between position-relative z-1">
                            <span class="badge bg-secondary text-white-50 px-3 py-2 rounded-pill">
                                <i class="bi bi-collection me-1"></i> View Modules
                            </span>
                            <i class="bi bi-arrow-right-circle text-theme fs-4"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @empty
        <div class="col-12 py-5 text-center">
            <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
            <h3 class="mt-3 text-muted">No Courses Available</h3>
            <p class="text-muted">You are not enrolled in any courses yet. Please wait for an administrator to assign you to a class or course.</p>
        </div>
    @endforelse
</div>
@endsection
