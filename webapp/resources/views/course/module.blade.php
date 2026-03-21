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
    <div class="card mb-4 border-0">
        <div class="m-1 bg-inverse bg-opacity-10">
            <div class="position-relative overflow-hidden" style="height: 200px">
                @php
                    $isWorkshop = $module['type'] === 'workshop';
                    $coverImage = match(true) {
                        str_contains($module['slug'], 'soc') => asset('img/workshops/network_analysis.png'),
                        str_contains($module['slug'], 'malware') => asset('img/workshops/malware_analysis.png'),
                        str_contains($module['slug'], 'cti') => asset('img/workshops/cti_automation.png'),
                        str_contains($module['slug'], 'fastmcp') => asset('img/workshops/fastmcp_deploy.png'),
                        str_contains($module['slug'], 'threat') => asset('img/workshops/threat_hunting.png'),
                        default => null
                    };
                @endphp
                @if($coverImage && $isWorkshop)
                    <img src="{{ $coverImage }}" class="card-img rounded-0 w-100 h-100" style="object-fit: cover; object-position: center 30%;" alt="">
                @else
                    <div class="w-100 h-100 bg-dark" style="background: linear-gradient(135deg, rgba(30,32,34,1) 0%, rgba(20,21,22,1) 100%);"></div>
                @endif
                
                <div class="card-img-overlay text-white text-center bg-gray-900 {{ $isWorkshop ? 'bg-opacity-75' : 'bg-opacity-50' }} d-flex flex-column align-items-center justify-content-center">
                    <div class="my-2">
                        <div class="{{ $isWorkshop ? 'bg-warning' : 'bg-theme' }} text-dark rounded-circle d-flex align-items-center justify-content-center mx-auto shadow" style="width: 64px; height: 64px; font-size: 1.8rem;">
                            <i class="bi {{ $module['icon'] }}"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="fw-bold fs-3 text-shadow px-2 mb-1">{{ $module['title'] }}</h1>
                        <div class="small fw-semibold text-white text-opacity-75 letter-spacing-1 mt-1 {{ $isWorkshop ? 'text-warning' : '' }}">
                            {{ $isWorkshop ? 'WORKSHOP ' . sprintf('%02d', $module['number']) : 'MODULE ' . sprintf('%02d', $module['number']) }}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-body py-3 px-3 fs-6 d-flex flex-column align-items-center justify-content-center">
                <div class="row w-100 text-center align-items-center">
                    <div class="col-4 border-end border-secondary">
                        <div class="fw-bold fs-4 text-inverse">{{ count($lessons ?? []) }}</div>
                        <div class="fs-10px fw-semibold text-muted text-uppercase">SECTIONS</div>
                    </div>
                    <div class="col-4 border-end border-secondary d-flex flex-column justify-content-center align-items-center" style="min-height: 48px;">
                        @if($isWorkshop)
                            @php
                                $notebookFilename = sprintf('%02d', $module['number']) . '_' . substr($module['folder'], 12) . '.ipynb';
                                $colabUrl = config('course.workshop_github_base_url') . $module['folder'] . '/' . $notebookFilename;
                            @endphp
                            <a href="{{ $colabUrl }}" target="_blank" class="btn btn-outline-warning btn-sm d-flex align-items-center justify-content-center gap-2 m-0 mt-1" 
                               style="border-color:#f59e0b; color:#f59e0b; font-weight: 600; width: fit-content; text-transform: uppercase; font-size: 10px;">
                                <img src="https://colab.research.google.com/assets/colab-badge.svg" alt="Open In Colab" style="height: 14px;" />
                                Launch Interactive Environment
                            </a>
                        @else
                            <div class="fw-bold fs-4 text-inverse">{{ count(config("course.sections")) * 20 }}</div>
                            <div class="fs-10px fw-semibold text-muted text-uppercase">ESTIMATED MINS</div>
                        @endif
                    </div>
                    <div class="col-4 d-flex flex-column justify-content-center align-items-center" style="min-height: 48px;">
                        <div class="fw-bold fs-4 text-success"><i class="bi bi-check-circle-fill"></i></div>
                        <div class="fs-10px fw-semibold text-muted text-uppercase">STATUS</div>
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