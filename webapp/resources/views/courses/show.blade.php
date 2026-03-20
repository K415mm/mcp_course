@extends('layouts.app')

@section('title', $course['title'] . ' Modules')

@section('content')
<div class="d-flex align-items-center mb-4">
    <div>
        <h1 class="page-header mb-0">{{ $course['title'] }}</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">Courses</a></li>
            <li class="breadcrumb-item active">{{ $course['title'] }}</li>
        </ol>
    </div>
</div>

@if(!empty($course['description']))
    <div class="mb-4 text-muted fs-5">{{ $course['description'] }}</div>
@endif

<div class="mb-5">
    <h4 class="mb-3">Course Modules</h4>
    <div class="row">
        @forelse($modules as $item)
            <div class="col-xl-4 col-lg-6 mb-4">
                <a href="{{ route('course.module', $item['slug']) }}" class="text-decoration-none">
                    <div class="card h-100 module-card border-0 {{ $item['locked'] ? 'opacity-50' : '' }}">
                        <div class="m-1 bg-inverse bg-opacity-10 h-100 d-flex flex-column">
                            <div class="position-relative overflow-hidden" style="height: 150px">
                                @php
                                    $isWorkshop = $item['type'] === 'workshop';
                                    $coverImage = match(true) {
                                        str_contains($item['slug'], 'soc') => asset('img/workshops/network_analysis.png'),
                                        str_contains($item['slug'], 'malware') => asset('img/workshops/malware_analysis.png'),
                                        str_contains($item['slug'], 'cti') => asset('img/workshops/cti_automation.png'),
                                        str_contains($item['slug'], 'fastmcp') => asset('img/workshops/fastmcp_deploy.png'),
                                        str_contains($item['slug'], 'threat') => asset('img/workshops/threat_hunting.png'),
                                        default => null
                                    };
                                @endphp
                                @if($coverImage && $isWorkshop)
                                    <img src="{{ $coverImage }}" class="card-img rounded-0 w-100 h-100" style="object-fit: cover;" alt="">
                                @else
                                    <div class="w-100 h-100 bg-dark" style="background: linear-gradient(135deg, rgba(30,32,34,1) 0%, rgba(20,21,22,1) 100%);"></div>
                                @endif
                                
                                <div class="card-img-overlay text-white text-center bg-gray-900 {{ $isWorkshop ? 'bg-opacity-75' : 'bg-opacity-50' }} d-flex flex-column align-items-center justify-content-center">
                                    
                                    @if($item['locked'])
                                        <div class="position-absolute top-0 end-0 m-3 text-danger" title="Locked">
                                            <i class="bi bi-lock-fill fs-4" style="text-shadow: 0 2px 4px rgba(0,0,0,0.8);"></i>
                                        </div>
                                    @endif

                                    <div class="my-2">
                                        <div class="{{ $item['locked'] ? 'bg-secondary' : 'bg-theme' }} text-dark rounded-circle d-flex align-items-center justify-content-center mx-auto shadow" style="width: 50px; height: 50px; font-size: 1.2rem;">
                                            <i class="{{ $item['icon'] ?? 'bi bi-book' }}"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-6 text-shadow px-2">{{ $item['title'] }}</div>
                                        <div class="small fw-semibold text-white text-opacity-75 letter-spacing-1 mt-1">
                                            {{ $isWorkshop ? 'WORKSHOP' : 'MODULE ' . sprintf('%02d', $item['number']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body py-3 px-3 fs-6 d-flex flex-column flex-grow-1 align-items-center justify-content-center">
                                <div class="row w-100 text-center">
                                    <div class="col-6 border-end border-secondary">
                                        <div class="fw-bold fs-5 text-inverse">{{ count($item['lessons'] ?? []) }}</div>
                                        <div class="fs-10px fw-semibold text-muted text-uppercase">Lessons</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="fw-bold fs-5 text-inverse">--</div>
                                        <div class="fs-10px fw-semibold text-muted text-uppercase">Mins</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12"><p class="text-muted">No modules available found in this course.</p></div>
        @endforelse
    </div>
</div>
@endsection
