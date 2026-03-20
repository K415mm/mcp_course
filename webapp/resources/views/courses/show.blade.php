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
                    <div class="card h-100 module-card {{ $item['locked'] ? 'opacity-50' : 'border-theme' }}">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <div class="w-40px h-40px rounded d-flex align-items-center justify-content-center me-3 {{ $item['locked'] ? 'bg-secondary' : 'bg-theme text-dark' }}">
                                    <i class="{{ $item['icon'] ?? 'bi bi-book' }} fs-4"></i>
                                </div>
                                <div class="flex-1">
                                    <h5 class="mb-0 text-inverse">
                                        {{ $item['type'] === 'workshop' ? 'Workshop' : 'Module ' . sprintf('%02d', $item['number']) }}: 
                                        {{ $item['title'] }}
                                    </h5>
                                </div>
                                @if($item['locked'])
                                    <div class="ms-2 text-danger" title="Locked"><i class="bi bi-lock-fill fs-4"></i></div>
                                @endif
                            </div>
                            <div class="text-muted small mt-3">
                                <i class="bi bi-journal-text me-1"></i> {{ count($item['lessons'] ?? []) }} Lessons
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
