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
                <div class="card h-100 module-card border-0">
                    <div class="m-1 bg-inverse bg-opacity-10 h-100 d-flex flex-column">
                        <div class="position-relative overflow-hidden" style="height: 165px">
                            @php
                                $coverImage = match(true) {
                                    str_contains($course['slug'], 'soc') => asset('img/workshops/network_analysis.png'),
                                    str_contains($course['slug'], 'malware') => asset('img/workshops/malware_analysis.png'),
                                    default => null
                                };
                            @endphp
                            @if($coverImage)
                                <img src="{{ $coverImage }}" class="card-img rounded-0 w-100 h-100" style="object-fit: cover;" alt="">
                            @else
                                <div class="w-100 h-100 bg-dark" style="background: linear-gradient(135deg, #1f2225 0%, #121415 100%);"></div>
                            @endif
                            <div class="card-img-overlay text-white text-center bg-gray-900 bg-opacity-75 d-flex flex-column align-items-center justify-content-center">
                                <div class="my-2">
                                    <div class="bg-theme text-dark rounded-circle d-flex align-items-center justify-content-center mx-auto shadow" style="width: 56px; height: 56px; font-size: 1.5rem;">
                                        <i class="bi bi-journal-bookmark-fill"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-bold fs-5 text-shadow">{{ $course['title'] }}</div>
                                    <div class="small fw-semibold text-white text-opacity-75 letter-spacing-1">ONLINE COURSE</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body py-4 px-3 fs-6 d-flex flex-column flex-grow-1">
                            <div class="text-center text-muted mb-4 small flex-grow-1 d-flex flex-column justify-content-center">
                                {{ $course['description'] ?? 'Learn core concepts, practical skills, and access hands-on workshops in this comprehensive training course.' }}
                            </div>
                            <!-- Static metrics for visual enhancement -->
                            <div class="row text-center mt-auto">
                                <div class="col-4">
                                    <div class="fw-bold fs-5 text-theme">--</div>
                                    <div class="fs-10px fw-semibold text-muted text-uppercase">Modules</div>
                                </div>
                                <div class="col-4 border-start border-end border-secondary">
                                    <div class="fw-bold fs-5 text-theme">--</div>
                                    <div class="fs-10px fw-semibold text-muted text-uppercase">Workshops</div>
                                </div>
                                <div class="col-4">
                                    <div class="fw-bold fs-5 text-theme">--</div>
                                    <div class="fs-10px fw-semibold text-muted text-uppercase">Hours</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-arrow">
                        <div class="card-arrow-top-left"></div>
                        <div class="card-arrow-top-right"></div>
                        <div class="card-arrow-bottom-left"></div>
                        <div class="card-arrow-bottom-right"></div>
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
