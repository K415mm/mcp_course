@extends('layouts.app')

@section('title', $lessonTitle . ' — ' . config('course.title'))

@section('content')
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-theme">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('course.index') }}" class="text-theme">All Modules</a></li>
            <li class="breadcrumb-item"><a href="{{ route('course.module', $module['slug']) }}"
                    class="text-theme">{{ $module['title'] }}</a></li>
            <li class="breadcrumb-item active">{{ $lessonTitle }}</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Lesson List Sidebar -->
        <div class="col-xl-3 col-lg-4 mb-4">
            <div class="d-flex align-items-center mb-3 gap-2">
                <i class="bi {{ $module['icon'] }} text-theme"></i>
                <span class="fw-semibold fs-13px text-inverse">{{ $module['title'] }}</span>
            </div>

            @foreach($lessons as $sectionKey => $sectionData)
                <div class="card mb-3">
                    <div class="card-body p-0">
                        <div class="px-3 py-2 border-bottom border-secondary">
                            <span class="fw-semibold fs-11px text-uppercase text-muted" style="letter-spacing:.07em;">
                                <i class="bi bi-folder2 me-1"></i>{{ $sectionData['label'] }}
                            </span>
                        </div>
                        <div class="py-1">
                            @foreach($sectionData['lessons'] as $l)
                                <a href="{{ route('course.lesson', [$module['slug'], $sectionKey, $l['slug']]) }}" class="d-block text-decoration-none text-inverse px-3 py-2 lesson-nav-item
                                          {{ ($l['slug'] === $lessonSlug && $sectionKey === $section) ? 'active' : '' }}">
                                    <i class="bi bi-file-earmark-text me-2 opacity-40 fs-12px"></i>
                                    <span class="fs-12px">{{ $l['title'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                    <div class="card-arrow">
                        <div class="card-arrow-top-left"></div>
                        <div class="card-arrow-top-right"></div>
                        <div class="card-arrow-bottom-left"></div>
                        <div class="card-arrow-bottom-right"></div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Main Lesson Content -->
        <div class="col-xl-9 col-lg-8">
            <!-- Section badge -->
            <div class="mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                @php $sections = config('course.sections'); @endphp
                <span class="badge bg-dark border border-secondary text-muted px-3 py-2 fs-11px">
                    <i class="bi bi-folder2-open me-1"></i>
                    {{ $sections[$section] ?? ucfirst($section) }}
                </span>
                <a href="{{ route('course.module', $module['slug']) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Module
                </a>
            </div>

            <!-- Dynamic Content Rendering -->
            @switch($contentType)
                @case('quiz')
                    @include('course.partials.quiz-renderer')
                    @break
                @case('video')
                    @include('course.partials.video-card')
                    @break
                @case('workshop')
                    @include('course.partials.workshop-steps')
                    @break
                @case('slides')
                    @include('course.partials.slides-deck')
                    @break
                @default
                    <!-- Lesson Content Card (Default Markdown) -->
                    <div class="card mb-4" style="border-color:rgba(255,255,255,.07);">
                        <div class="card-body p-4 p-lg-5">
                            <div class="md-content">
                                {!! $contentHtml !!}
                            </div>
                        </div>
                        <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                    </div>
            @endswitch

            <!-- Prev / Next Navigation -->
            <div class="d-flex justify-content-between align-items-center mt-2">
                @if($prevLesson)
                    <a href="{{ route('course.lesson', [$module['slug'], $prevLesson['section'], $prevLesson['slug']]) }}"
                        class="btn btn-outline-theme">
                        <i class="bi bi-chevron-left me-1"></i> {{ $prevLesson['title'] }}
                    </a>
                @else
                    <div></div>
                @endif

                @if($nextLesson)
                    <a href="{{ route('course.lesson', [$module['slug'], $nextLesson['section'], $nextLesson['slug']]) }}"
                        class="btn btn-theme">
                        {{ $nextLesson['title'] }} <i class="bi bi-chevron-right ms-1"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection