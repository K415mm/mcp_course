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
                    <div class="card module-card h-100 position-relative">
                        @if($mod['locked'])
                            <div class="position-absolute w-100 h-100 top-0 start-0 d-flex flex-column align-items-center justify-content-center rounded"
                                style="background: rgba(0,0,0,0.75); z-index: 10;">
                                <i class="bi bi-lock-fill fs-2 text-white mb-2" style="opacity: 0.9;"></i>
                                <span class="badge bg-dark border border-secondary px-3 py-2">Requires Upgrade</span>
                            </div>
                        @endif
                        <div class="card-body {{ $mod['locked'] ? 'opacity-25' : '' }}">
                            <div class="d-flex gap-3 mb-3">
                                <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-3"
                                    style="width:48px;height:48px;background:rgba(var(--bs-theme-rgb),.12);">
                                    <span class="fw-bold fs-18px text-theme">{{ sprintf('%02d', $mod['number']) }}</span>
                                </div>
                                <div>
                                    <span class="badge bg-theme text-dark module-badge">MODULE</span>
                                    <h6 class="mt-1 mb-0 fw-semibold text-inverse">{{ $mod['title'] }}</h6>
                                </div>
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                @foreach(config('course.sections') as $key => $label)
                                    <span class="badge bg-dark text-muted border border-secondary fs-10px">
                                        <i class="bi bi-file-text me-1"></i>{{ $label }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 pt-0">
                            <span class="text-theme fs-12px fw-semibold">
                                <i class="bi bi-arrow-right-circle me-1"></i>View Module
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

    <!-- Workshops Section -->
    @if(count($workshops))
        <h6 class="text-muted text-uppercase fw-semibold fs-11px mb-3">
            Workshops — {{ count($workshops) }} total
        </h6>
        <div class="row">
            @foreach($workshops as $ws)
                <div class="col-xl-4 col-md-6 mb-4">
                    <a href="{{ route('course.module', $ws['slug']) }}" class="text-decoration-none">
                        <div class="card module-card h-100 position-relative" style="border-color:rgba(245,158,11,.2);">
                            @if($ws['locked'])
                                <div class="position-absolute w-100 h-100 top-0 start-0 d-flex flex-column align-items-center justify-content-center rounded"
                                    style="background: rgba(0,0,0,0.75); z-index: 10;">
                                    <i class="bi bi-lock-fill fs-2 text-white mb-2" style="opacity: 0.9;"></i>
                                    <span class="badge bg-dark border border-secondary px-3 py-2">Requires Upgrade</span>
                                </div>
                            @endif
                            <div class="card-body {{ $ws['locked'] ? 'opacity-25' : '' }}">
                                <div class="d-flex gap-3 mb-3">
                                    <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-3"
                                        style="width:48px;height:48px;background:rgba(245,158,11,.12);">
                                        <i class="bi bi-tools" style="color:#f59e0b;font-size:1.3rem;"></i>
                                    </div>
                                    <div>
                                        <span class="badge module-badge" style="background:#f59e0b;color:#000;">WORKSHOP</span>
                                        <h6 class="mt-1 mb-0 fw-semibold text-inverse">{{ $ws['title'] }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-0 pt-0">
                                <span class="fs-12px fw-semibold" style="color:#f59e0b;">
                                    <i class="bi bi-arrow-right-circle me-1"></i>View Workshop
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
@endsection