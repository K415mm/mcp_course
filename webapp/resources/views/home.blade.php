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

    <!-- Cinematic Plane Animation Overlay -->
    <img id="cinematic-plane" src="{{ asset('img/workshops/fastmcp_deploy.png') }}" 
         alt="AI Plane" 
         style="position: fixed; bottom: -300px; left: -300px; width: 400px; z-index: 9999; pointer-events: none; filter: drop-shadow(0 20px 30px rgba(0,0,0,0.8)); opacity: 0; transform: rotate(15deg);" />

@endsection

@stack('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof gsap !== 'undefined') {
            const plane = document.getElementById('cinematic-plane');
            
            // GSAP Timeline for the cinematic flyover
            gsap.to(plane, {
                opacity: 0.9,
                duration: 0.5,
                ease: "power2.inOut"
            });

            gsap.to(plane, {
                x: window.innerWidth + 600,
                y: -(window.innerHeight + 600),
                scale: 1.5,
                duration: 3.5,
                ease: "power1.inOut",
                delay: 0.2,
                onComplete: () => {
                    plane.style.display = 'none'; // hide when done
                }
            });
        }
    });
</script>