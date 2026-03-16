@extends('layouts.app')

@section('title', $module['title'] . ' — ' . config('course.title'))

@section('content')
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-theme">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('course.index') }}" class="text-theme">All Modules</a></li>
            <li class="breadcrumb-item active">{{ $module['title'] }}</li>
        </ol>
    </nav>

    <!-- Module Header -->
    <div class="card mb-4" style="border-color:rgba(var(--bs-theme-rgb),.3);">
        <div class="card-body d-flex flex-column flex-md-row align-items-md-center gap-3 p-3">
            <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                style="width:56px;height:56px;background:rgba(var(--bs-theme-rgb),.12);">
                <i class="bi {{ $module['icon'] }}" style="font-size:1.6rem;color:var(--bs-theme);"></i>
            </div>
            <div class="flex-grow-1">
                <div class="mb-1">
                    @if($module['type'] === 'module')
                        <span class="badge bg-theme text-dark module-badge">MODULE
                            {{ sprintf('%02d', $module['number']) }}</span>
                    @else
                        <span class="badge module-badge" style="background:#f59e0b;color:#000;">WORKSHOP
                            {{ sprintf('%02d', $module['number']) }}</span>
                    @endif
                </div>
                <h1 class="h4 fw-bold mb-0 text-inverse">{{ $module['title'] }}</h1>
            </div>
            
            @if($module['type'] === 'workshop')
                @php
                    $notebookFilename = sprintf('%02d', $module['number']) . '_' . substr($module['folder'], 12) . '.ipynb';
                    $colabUrl = config('course.workshop_github_base_url') . $module['folder'] . '/' . $notebookFilename;
                @endphp
                <div class="flex-shrink-0 mt-3 mt-md-0">
                    <a href="{{ $colabUrl }}" target="_blank" class="btn btn-outline-warning d-flex align-items-center gap-2" 
                       style="border-color:#f59e0b; color:#f59e0b; font-weight: 600;">
                        <img src="https://colab.research.google.com/assets/colab-badge.svg" alt="Open In Colab" style="height: 20px;" />
                        Launch Interactive Environment
                    </a>
                </div>
            @endif
        </div>
        <div class="card-arrow">
            <div class="card-arrow-top-left"></div>
            <div class="card-arrow-top-right"></div>
            <div class="card-arrow-bottom-left"></div>
            <div class="card-arrow-bottom-right"></div>
        </div>
    </div>

    <div class="row">
        <!-- Lesson List Sidebar -->
        <div class="col-xl-3 col-md-4 mb-4">
            @foreach($lessons as $sectionKey => $sectionData)
                <div class="card mb-3">
                    <div class="card-body p-0">
                        <div class="px-3 py-2 border-bottom border-secondary d-flex align-items-center">
                            <i class="bi bi-folder2 me-2 text-theme fs-14px"></i>
                            <span class="fw-semibold fs-12px text-uppercase"
                                style="letter-spacing:.08em;">{{ $sectionData['label'] }}</span>
                            <span class="badge bg-dark text-muted ms-auto">{{ count($sectionData['lessons']) }}</span>
                        </div>
                        <div class="py-1">
                            @foreach($sectionData['lessons'] as $lesson)
                                <a href="{{ route('course.lesson', [$module['slug'], $sectionKey, $lesson['slug']]) }}"
                                    class="d-block text-decoration-none text-inverse px-3 py-2 lesson-nav-item
                                          {{ (isset($lessonSlug) && $lesson['slug'] === $lessonSlug && isset($section) && $section === $sectionKey) ? 'active' : '' }}">
                                    <i class="bi bi-file-earmark-text me-2 opacity-50 fs-12px"></i>
                                    <span class="fs-12px">{{ $lesson['title'] }}</span>
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

            @if(empty($lessons))
                <div class="alert alert-info fs-12px">No lesson files found in this module directory.</div>
            @endif
        </div>

        <!-- Module Overview -->
        <div class="col-xl-9 col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="fw-semibold mb-4 d-flex align-items-center gap-2">
                        <i class="bi bi-file-text text-theme"></i> Module Overview
                    </h5>
                    <div class="md-content">
                        {!! $overviewHtml !!}
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

    <!-- Cinematic Threat Hunting Animation Overlay (for Advanced Modules/Workshops) -->
    @if($cinematicAnimationsEnabled ?? true)
        @if(in_array($module['slug'], ['workshop-02-threat-hunting', 'workshop-04-malware-analysis']) || $module['number'] >= 3)
            <img id="cinematic-threat" src="{{ asset('img/workshops/threat_hunting.png') }}" 
                 alt="Cyber Operations" 
                 style="position: fixed; bottom: -800px; right: -50px; width: 600px; z-index: 0; pointer-events: none; opacity: 0; filter: drop-shadow(0 -20px 40px rgba(0,0,0,0.8)); transform: rotate(-5deg);" />
        @endif
    @endif

@endsection

@if($cinematicAnimationsEnabled ?? true)
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof gsap !== 'undefined') {
                const threatImg = document.getElementById('cinematic-threat');
                if (threatImg) {
                    // GSAP Timeline for the cinematic rising effect
                    gsap.to(threatImg, {
                        bottom: "-100px",  // Rise up from the bottom
                        opacity: 0.12,     // Keep it subtle as a background layer
                        rotation: 0,       // Straighten out as it rises
                        duration: 3,
                        ease: "power3.out",
                        delay: 0.2
                    });
                }
            }
        });
    </script>
    @endpush
@endif