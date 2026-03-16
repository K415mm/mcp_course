@extends('layouts.app')

@section('title', config('course.title') . ' — Dashboard')

@section('content')
    <div class="row mb-4">
        <!-- Hero Header -->
        <div class="col-12">
            <div class="card mb-4"
                style="background: linear-gradient(135deg, #0a1628 0%, #11234b 50%, #0a1628 100%); border: 1px solid rgba(var(--bs-theme-rgb),.3);">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="badge bg-theme text-dark mb-2 px-3 py-2 fs-11px fw-bold">← RGSOC ACADEMY</div>
                        <h1 class="text-white fw-bold mb-2" style="font-size:2rem;">Agentic AI & MCP for Cyber Defense</h1>
                        <p class="text-white text-opacity-70 mb-3" style="max-width:600px;">
                            Master Model Context Protocol, build autonomous security agents, and deploy AI-driven cyber
                            defense workflows.
                            {{ count($modules) }} Modules · {{ count($workshops) }} Workshops
                        </p>
                        <a href="{{ route('course.index') }}" class="btn btn-theme px-4 fw-semibold">
                            <i class="bi bi-play-circle me-2"></i>Start Learning
                        </a>
                    </div>
                    <div class="d-none d-lg-flex align-items-center justify-content-center ms-4"
                        style="width:120px;height:120px;border-radius:50%;border:3px solid rgba(var(--bs-theme-rgb),.4);background:rgba(var(--bs-theme-rgb),.08);">
                        <i class="bi bi-cpu" style="font-size:3rem;color:var(--bs-theme);"></i>
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

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3"
                        style="width:52px;height:52px;background:rgba(var(--bs-theme-rgb),.15);">
                        <i class="bi bi-book fs-4" style="color:var(--bs-theme);"></i>
                    </div>
                    <div>
                        <div class="fs-24px fw-bold">{{ count($modules) }}</div>
                        <div class="text-muted fs-12px">Modules</div>
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
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3"
                        style="width:52px;height:52px;background:rgba(245,158,11,.15);">
                        <i class="bi bi-tools fs-4" style="color:#f59e0b;"></i>
                    </div>
                    <div>
                        <div class="fs-24px fw-bold">{{ count($workshops) }}</div>
                        <div class="text-muted fs-12px">Workshops</div>
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
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3"
                        style="width:52px;height:52px;background:rgba(16,185,129,.15);">
                        <i class="bi bi-shield-check fs-4" style="color:#10b981;"></i>
                    </div>
                    <div>
                        <div class="fs-24px fw-bold">MCP</div>
                        <div class="text-muted fs-12px">Protocol Focus</div>
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
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3"
                        style="width:52px;height:52px;background:rgba(139,92,246,.15);">
                        <i class="bi bi-robot fs-4" style="color:#8b5cf6;"></i>
                    </div>
                    <div>
                        <div class="fs-24px fw-bold">AI</div>
                        <div class="text-muted fs-12px">Agentic Focus</div>
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

    <!-- Modules Grid -->
    <div class="row mb-2">
        <div class="col-12">
            <h5 class="fw-semibold mb-3"><i class="bi bi-book me-2 text-theme"></i>Course Modules</h5>
        </div>
        @foreach($modules as $mod)
            <div class="col-xl-4 col-md-6 mb-4">
                <a href="{{ route('course.module', $mod['slug']) }}" class="text-decoration-none">
                    <div class="card module-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start mb-3">
                                <div class="d-flex align-items-center justify-content-center rounded-3 me-3 flex-shrink-0"
                                    style="width:44px;height:44px;background:rgba(var(--bs-theme-rgb),.15);">
                                    <i class="bi bi-book" style="color:var(--bs-theme);font-size:1.2rem;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="badge bg-theme text-dark module-badge mb-1">MODULE
                                        {{ sprintf('%02d', $mod['number']) }}</span>
                                    <h6 class="mb-0 fw-semibold text-inverse">{{ $mod['title'] }}</h6>
                                </div>
                            </div>
                            <p class="text-muted fs-12px mb-3">Click to explore lessons, practicals, and examples.</p>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-theme fs-12px fw-semibold">
                                    <i class="bi bi-arrow-right-circle me-1"></i>Open Module
                                </span>
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
        @endforeach
    </div>

    <!-- Workshops Grid -->
    @if(count($workshops))
        <div class="row">
            <div class="col-12">
                <h5 class="fw-semibold mb-3"><i class="bi bi-tools me-2" style="color:#f59e0b;"></i>Practical Workshops</h5>
            </div>
            @foreach($workshops as $ws)
                <div class="col-xl-4 col-md-6 mb-4">
                    <a href="{{ route('course.module', $ws['slug']) }}" class="text-decoration-none">
                        <div class="card module-card h-100" style="border-color:rgba(245,158,11,.2);">
                            <div class="card-body">
                                <div class="d-flex align-items-start mb-3">
                                    <div class="d-flex align-items-center justify-content-center rounded-3 me-3 flex-shrink-0"
                                        style="width:44px;height:44px;background:rgba(245,158,11,.15);">
                                        <i class="bi bi-tools" style="color:#f59e0b;font-size:1.2rem;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <span class="badge module-badge mb-1" style="background:#f59e0b;color:#000;">WORKSHOP
                                            {{ sprintf('%02d', $ws['number']) }}</span>
                                        <h6 class="mb-0 fw-semibold text-inverse">{{ $ws['title'] }}</h6>
                                    </div>
                                </div>
                                <p class="text-muted fs-12px mb-3">Hands-on workshop with real-world scenarios.</p>
                                <span class="fs-12px fw-semibold" style="color:#f59e0b;">
                                    <i class="bi bi-arrow-right-circle me-1"></i>Open Workshop
                                </span>
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
            @endforeach
        </div>
    @endif

    @if($cinematicAnimationsEnabled ?? true)
        <!-- Cinematic Network Analysis Background Stream -->
        <img id="cinematic-network" src="{{ asset('img/workshops/network_analysis.png') }}" 
             alt="Data Stream" 
             style="position: fixed; top: -100px; right: -200px; width: 500px; z-index: 0; pointer-events: none; filter: drop-shadow(0 0 40px rgba(16,185,129,0.3)); opacity: 0;" />
    
        <!-- Cinematic Plane Animation Overlay -->
        <img id="cinematic-plane" src="{{ asset('img/workshops/fastmcp_deploy.png') }}" 
             alt="AI Plane" 
             style="position: fixed; bottom: -300px; left: -300px; width: 350px; z-index: 9999; pointer-events: none; filter: drop-shadow(0 20px 30px rgba(0,0,0,0.8)); opacity: 0; transform: rotate(15deg);" />
    @endif

@endsection

@if($cinematicAnimationsEnabled ?? true)
    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof gsap !== 'undefined') {
                
                // 1. Network Analysis - Slow Floating Background
                const network = document.getElementById('cinematic-network');
                if (network) {
                    gsap.to(network, { opacity: 0.15, duration: 2, ease: "power1.inOut" });
                    gsap.to(network, {
                        y: 100,
                        x: -50,
                        rotation: 10,
                        duration: 15,
                        repeat: -1,
                        yoyo: true,
                        ease: "sine.inOut"
                    });
                }
    
                // 2. FastMCP Plane - Multi-step Cinematic Flyby
                const plane = document.getElementById('cinematic-plane');
                if (plane) {
                    // Calculate center screen coordinates based on starting position
                    const centerX = (window.innerWidth / 2) + 150;
                    const centerY = -(window.innerHeight / 2) - 150;
    
                    let tl = gsap.timeline({ delay: 0.5 });
                    
                    // Sweep into the center of the screen
                    tl.to(plane, {
                        x: centerX,
                        y: centerY,
                        opacity: 1,
                        scale: 1.2,
                        duration: 2.5,
                        ease: "power2.out"
                    })
                    // The "Look at the user" pause (slight hover/scale)
                    .to(plane, {
                        scale: 1.4,
                        rotation: 12,
                        duration: 1.5,
                        ease: "sine.inOut"
                    })
                    // Shoot off the screen quickly
                    .to(plane, {
                        x: window.innerWidth + 800,
                        y: -(window.innerHeight + 800),
                        scale: 0.8,
                        opacity: 0,
                        duration: 0.8,
                        ease: "power3.in",
                        onComplete: () => {
                            plane.style.display = 'none';
                        }
                    });
                }
            }
        });
    </script>
@endif