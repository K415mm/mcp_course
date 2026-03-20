@extends('layouts.app')

@section('title', 'All Modules — ' . config('course.title'))

@section('content')
    <!-- Page Header -->
    <div class="d-flex align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-theme">Dashboard</a></li>
                    <li class="breadcrumb-item active">All Modules</li>
                </ol>
            </nav>
            <h1 class="h4 fw-bold mb-0">Course Curriculum</h1>
        </div>
    </div>

    @php
        $modules = array_filter($items, fn($i) => $i['type'] === 'module');
        $workshops = array_filter($items, fn($i) => $i['type'] === 'workshop');
    @endphp

    <!-- Modules Section -->
    <h6 class="text-muted text-uppercase fw-semibold fs-11px mb-3 letter-spacing-2">
        Modules — {{ count($modules) }} total
    </h6>
    <div class="row mb-5">
        @foreach($modules as $mod)
            <div class="col-xl-4 col-md-6 mb-4">
                <a href="{{ route('course.module', $mod['slug']) }}" class="text-decoration-none">
                    <div class="card h-100 module-card border-0 {{ $mod['locked'] ? 'opacity-50' : '' }}">
                        <div class="m-1 bg-inverse bg-opacity-10 h-100 d-flex flex-column">
                            <div class="position-relative overflow-hidden" style="height: 150px">
                                @php
                                    $coverImage = match(true) {
                                        str_contains($mod['slug'], 'soc') => asset('img/workshops/network_analysis.png'),
                                        str_contains($mod['slug'], 'malware') => asset('img/workshops/malware_analysis.png'),
                                        str_contains($mod['slug'], 'cti') => asset('img/workshops/cti_automation.png'),
                                        str_contains($mod['slug'], 'fastmcp') => asset('img/workshops/fastmcp_deploy.png'),
                                        str_contains($mod['slug'], 'threat') => asset('img/workshops/threat_hunting.png'),
                                        default => null
                                    };
                                @endphp
                                @if($coverImage)
                                    <img src="{{ $coverImage }}" class="card-img rounded-0 w-100 h-100" style="object-fit: cover;" alt="">
                                @else
                                    <div class="w-100 h-100 bg-dark" style="background: linear-gradient(135deg, rgba(30,32,34,1) 0%, rgba(20,21,22,1) 100%);"></div>
                                @endif
                                
                                <div class="card-img-overlay text-white text-center bg-gray-900 bg-opacity-50 d-flex flex-column align-items-center justify-content-center">
                                    
                                    @if($mod['locked'])
                                        <div class="position-absolute top-0 end-0 m-3 text-danger" title="Locked">
                                            <i class="bi bi-lock-fill fs-4" style="text-shadow: 0 2px 4px rgba(0,0,0,0.8);"></i>
                                        </div>
                                    @endif

                                    <div class="my-2">
                                        <div class="{{ $mod['locked'] ? 'bg-secondary' : 'bg-theme' }} text-dark rounded-circle d-flex align-items-center justify-content-center mx-auto shadow" style="width: 50px; height: 50px; font-size: 1.2rem;">
                                            <span class="fw-bold fs-18px">{{ sprintf('%02d', $mod['number']) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-6 text-shadow px-2">{{ $mod['title'] }}</div>
                                        <div class="small fw-semibold text-white text-opacity-75 letter-spacing-1 mt-1">MODULE</div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body py-3 px-3 fs-6 d-flex flex-column flex-grow-1 align-items-center justify-content-center">
                                <div class="row w-100 text-center">
                                    <div class="col-6 border-end border-secondary">
                                        <div class="fw-bold fs-5 text-inverse">{{ count(config("course.sections")) }}</div>
                                        <div class="fs-10px fw-semibold text-muted text-uppercase">SECTIONS</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="fw-bold fs-5 text-inverse">{{ count(config("course.sections")) * 20 }}</div>
                                        <div class="fs-10px fw-semibold text-muted text-uppercase">MINS</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <!-- Workshops Section -->
    @if(count($workshops))
        <h6 class="text-muted text-uppercase fw-semibold fs-11px mb-3">
            Workshops — {{ count($workshops) }} total
        </h6>
        <div class="row">
            @foreach($workshops as $ws)
                <div class="col-xl-4 col-md-6 mb-4">
                    <a href="{{ route('course.module', $ws['slug']) }}" class="text-decoration-none">
                        <div class="card h-100 module-card border-0 {{ $ws['locked'] ? 'opacity-50' : '' }}">
                            <div class="m-1 bg-inverse bg-opacity-10 h-100 d-flex flex-column">
                                <div class="position-relative overflow-hidden" style="height: 150px">
                                    @php
                                        $cinematic = match(true) {
                                            str_contains($ws['slug'], 'cti-automation') => 'cti_automation.png',
                                            str_contains($ws['slug'], 'threat-hunting') => 'threat_hunting.png',
                                            str_contains($ws['slug'], 'network-analysis') => 'network_analysis.png',
                                            str_contains($ws['slug'], 'malware-analysis') => 'malware_analysis.png',
                                            str_contains($ws['slug'], 'fastmcp-deploy') => 'fastmcp_deploy.png',
                                            default => null
                                        };
                                    @endphp
                                    
                                    @if($cinematic)
                                        <img src="{{ asset('img/workshops/' . $cinematic) }}" class="card-img rounded-0 w-100 h-100" style="object-fit: cover;" alt="">
                                    @else
                                        <div class="w-100 h-100 bg-dark" style="background: linear-gradient(135deg, rgba(30,32,34,1) 0%, rgba(20,21,22,1) 100%);"></div>
                                    @endif
                                    
                                    <div class="card-img-overlay text-white text-center bg-gray-900 bg-opacity-75 d-flex flex-column align-items-center justify-content-center">
                                        
                                        @if($ws['locked'])
                                            <div class="position-absolute top-0 end-0 m-3 text-warning" title="Locked">
                                                <i class="bi bi-lock-fill fs-4" style="text-shadow: 0 2px 4px rgba(0,0,0,0.8);"></i>
                                            </div>
                                        @endif

                                        <div class="my-2">
                                            <div class="{{ $ws['locked'] ? 'bg-secondary' : 'bg-warning' }} text-dark rounded-circle d-flex align-items-center justify-content-center mx-auto shadow" style="width: 50px; height: 50px; font-size: 1.2rem;">
                                                <i class="bi bi-tools fs-4"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-bold fs-6 text-shadow px-2">{{ $ws['title'] }}</div>
                                            <div class="small fw-semibold text-white text-opacity-75 letter-spacing-1 mt-1 text-warning">WORKSHOP</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body py-3 px-3 fs-6 d-flex flex-column flex-grow-1 align-items-center justify-content-center">
                                    <div class="row w-100 text-center">
                                        <div class="col-6 border-end border-secondary">
                                            <div class="fw-bold fs-5 text-inverse">1</div>
                                            <div class="fs-10px fw-semibold text-muted text-uppercase">SCENARIO</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="fw-bold fs-5 text-inverse">120</div>
                                            <div class="fs-10px fw-semibold text-muted text-uppercase">MINS</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    @if($cinematicAnimationsEnabled ?? true)
        <!-- Cinematic CTI Automation Deep Scan Animation -->
        <img id="cinematic-cti" src="{{ asset('img/workshops/cti_automation.png') }}" 
             alt="CTI Radar" 
             style="position: fixed; bottom: 10%; right: -400px; width: 350px; z-index: 0; pointer-events: none; opacity: 0; filter: drop-shadow(0 0 50px rgba(59,130,246,0.5));" />
    @endif
@endsection

@if($cinematicAnimationsEnabled ?? true)
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof gsap !== 'undefined') {
                const ctiRadar = document.getElementById('cinematic-cti');
                if (ctiRadar) {
                    // Fade in and slide into view
                    gsap.to(ctiRadar, {
                        right: "50px",
                        opacity: 0.2,
                        duration: 3,
                        ease: "power3.out",
                        delay: 0.5
                    });
                    
                    // Continuous slow rotation like a radar scanning
                    gsap.to(ctiRadar, {
                        rotation: 360,
                        duration: 60,
                        repeat: -1,
                        ease: "linear"
                    });
                    
                    // Subtle pulse up and down
                    gsap.to(ctiRadar, {
                        y: -30,
                        duration: 4,
                        repeat: -1,
                        yoyo: true,
                        ease: "sine.inOut"
                    });
                }
            }
        });
    </script>
    @endpush
@endif