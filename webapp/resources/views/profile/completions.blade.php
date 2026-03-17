@extends('layouts.app')
@section('title', 'My Achievements — RAISEGUARD Academy')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-theme">Home</a></li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-inverse">
            <i class="bi bi-award text-theme me-2"></i>My Achievements
        </h4>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="card bg-dark border-secondary">
            <div class="card-body p-4 text-center pb-5">
                <h5 class="text-inverse text-uppercase fw-bold mb-1" style="letter-spacing: 2px;">RAISEGUARD Academy Completion</h5>
                <p class="text-muted fs-13px mb-4">Complete all 8 modules to unlock your final certificate and graduate gift.</p>
                
                @php
                    $totalModules = count($modules);
                    $completedCount = $completions->count();
                    $progressPercent = $totalModules > 0 ? ($completedCount / $totalModules) * 100 : 0;
                @endphp
                
                <div class="progress mb-3 bg-secondary" style="height: 12px; border-radius: 6px;">
                    <div class="progress-bar bg-theme" style="width: {{ $progressPercent }}%; box-shadow: 0 0 10px rgba(4, 236, 240, 0.5);"></div>
                </div>
                <div class="d-flex justify-content-between fs-12px text-muted fw-semibold">
                    <span>SYSTEM PROGRESS</span>
                    <span class="text-theme">{{ $completedCount }} / {{ $totalModules }} MODULES</span>
                </div>
            </div>
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
        </div>
    </div>
</div>

<h5 class="mt-5 mb-4 text-theme text-uppercase fw-bold" style="letter-spacing: 1px;"><i class="bi bi-grid-1x2 me-2"></i>Module Badges</h5>

<div class="row g-4">
    @foreach($modules as $mod)
        @php
            $isComplete = $completions->has($mod['slug']);
            $completionData = $isComplete ? $completions->get($mod['slug']) : null;
        @endphp
        <div class="col-xl-3 col-lg-4 col-sm-6">
            <div class="card h-100 {{ $isComplete ? 'border-theme' : 'border-secondary' }}" style="{{ $isComplete ? 'background: rgba(4, 236, 240, 0.03);' : 'opacity: 0.6;' }}">
                <div class="card-body text-center p-4">
                    {{-- Badge Display --}}
                    <div class="mb-3 position-relative mx-auto" style="width: 100px; height: 100px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 40px; {{ $isComplete ? 'border-color: rgba(4, 236, 240, 0.4); box-shadow: 0 0 20px rgba(4, 236, 240, 0.15);' : 'filter: grayscale(100%);' }}">
                        @if($isComplete)
                            <img src="{{ asset('img/badges/' . str_replace('module_0', 'm', strtolower($mod['slug'])) . '_badge.png') }}" 
                                 alt="Badge" style="width: 80%; height: auto;"
                                 onerror="this.outerHTML='🎖️'">
                        @else
                            <i class="bi bi-lock-fill text-muted" style="font-size: 2rem;"></i>
                        @endif
                        
                        {{-- Tag --}}
                        <div class="position-absolute" style="bottom: -10px; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; font-family: monospace; {{ $isComplete ? 'background: #04ecf0; color: #000;' : 'background: #333; color: #999;' }}">
                            M{{ sprintf('%02d', $mod['number']) }}
                        </div>
                    </div>
                    
                    <h6 class="text-inverse mt-4 mb-2 lh-sm" style="font-size: 14px;">{{ $mod['title'] }}</h6>
                    
                    @if($isComplete)
                        <div class="fs-12px text-theme">
                            <i class="bi bi-check2-circle me-1"></i>Earned {{ $completionData->completed_at->format('M j, Y') }}
                        </div>
                    @else
                        <div class="fs-12px text-muted">Awaiting Completion</div>
                    @endif
                </div>
                
                @if($isComplete)
                    <div class="card-footer bg-transparent border-0 text-center pb-3 pt-0">
                        <a href="{{ route('course.module', $mod['slug']) }}" class="btn btn-outline-theme btn-sm w-100"><i class="bi bi-arrow-repeat me-2"></i>Review Module</a>
                    </div>
                @else
                    <div class="card-footer bg-transparent border-0 text-center pb-3 pt-0">
                        <a href="{{ route('course.module', $mod['slug']) }}" class="btn btn-outline-secondary btn-sm w-100"><i class="bi bi-play-circle me-2"></i>Start Training</a>
                    </div>
                @endif
                <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
            </div>
        </div>
    @endforeach
</div>

@if($progressPercent == 100)
    <div class="alert bg-dark border-warning text-inverse mt-5 d-flex align-items-center mb-0">
        <i class="bi bi-trophy-fill text-warning fs-1 me-4"></i>
        <div>
            <h5 class="fw-bold text-warning mb-1">Congratulations!</h5>
            <p class="mb-0 fs-13px text-muted">You have completed all RAISEGUARD Academy modules. Your graduate certificate and gift details are being prepared and will be sent to your email.</p>
        </div>
        <a href="#" class="btn btn-warning ms-auto fw-bold text-dark px-4"><i class="bi bi-download me-2"></i>Download Certificate</a>
    </div>
@endif

@endsection

@push('scripts')
<style>
/* Add a subtle glow on hover for earned badges */
.border-theme:hover .position-relative {
    box-shadow: 0 0 30px rgba(4, 236, 240, 0.3) !important;
    transition: all 0.3s ease;
}
</style>
@endpush
