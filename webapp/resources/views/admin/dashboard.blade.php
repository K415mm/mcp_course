@extends('layouts.app')

@section('title', 'Admin Panel — ' . config('app.name'))

@section('content')
<!-- Admin Header Banner -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-inverse">
            <i class="bi bi-shield-lock text-theme me-2"></i>Admin Panel
        </h4>
        <p class="text-muted fs-13px mb-0">Manage courses, media, users, and quizzes.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.content.create') }}" class="btn btn-outline-theme btn-sm">
            <i class="bi bi-plus-circle me-1"></i>New Lesson
        </a>
        <a href="{{ route('admin.media.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-images me-1"></i>Media
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fs-13px mb-4">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Stats Row -->
<div class="row g-3 mb-4">
    @foreach([
        ['label'=>'Total Users',    'value'=>$stats['users'],     'icon'=>'bi-people',          'color'=>'var(--bs-theme)'],
        ['label'=>'Admins',         'value'=>$stats['admins'],    'icon'=>'bi-shield-check',     'color'=>'#dc3545'],
        ['label'=>'Modules',        'value'=>$stats['modules'],   'icon'=>'bi-book',             'color'=>'#3b82f6'],
        ['label'=>'Workshops',      'value'=>$stats['workshops'], 'icon'=>'bi-tools',            'color'=>'#f59e0b'],
        ['label'=>'Total Lessons',  'value'=>$stats['lessons'],   'icon'=>'bi-file-text',        'color'=>'#10b981'],
        ['label'=>'Media Files',    'value'=>$stats['media'],     'icon'=>'bi-images',           'color'=>'#8b5cf6'],
    ] as $stat)
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card h-100" style="border-color:rgba(255,255,255,.08);">
            <div class="card-body p-3 text-center">
                <div class="mb-1" style="font-size:1.6rem;color:{{ $stat['color'] }};">
                    <i class="bi {{ $stat['icon'] }}"></i>
                </div>
                <div class="fw-bold fs-22px text-inverse">{{ $stat['value'] }}</div>
                <div class="fs-11px text-muted">{{ $stat['label'] }}</div>
            </div>
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
        </div>
    </div>
    @endforeach
</div>

<!-- Quick Links -->
<div class="row g-3">
    <div class="col-md-3">
        <a href="{{ route('admin.content.index') }}" class="card text-decoration-none h-100" style="border-color:rgba(var(--bs-theme-rgb),.2);">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <i class="bi bi-file-earmark-text fs-2 text-theme"></i>
                <div>
                    <div class="fw-semibold text-inverse">Content Manager</div>
                    <div class="fs-12px text-muted">Browse, edit, create .md files</div>
                </div>
            </div>
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('admin.media.index') }}" class="card text-decoration-none h-100" style="border-color:rgba(var(--bs-theme-rgb),.2);">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <i class="bi bi-images fs-2 text-theme"></i>
                <div>
                    <div class="fw-semibold text-inverse">Media Library</div>
                    <div class="fs-12px text-muted">Upload images and videos</div>
                </div>
            </div>
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('admin.users.index') }}" class="card text-decoration-none h-100" style="border-color:rgba(var(--bs-theme-rgb),.2);">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <i class="bi bi-people fs-2 text-theme"></i>
                <div>
                    <div class="fw-semibold text-inverse">User Management</div>
                    <div class="fs-12px text-muted">Manage roles &amp; accounts</div>
                </div>
            </div>
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('home') }}" class="card text-decoration-none h-100" style="border-color:rgba(255,255,255,.08);">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <i class="bi bi-arrow-left-circle fs-2 text-muted"></i>
                <div>
                    <div class="fw-semibold text-inverse">Back to Academy</div>
                    <div class="fs-12px text-muted">Return to student view</div>
                </div>
            </div>
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
        </a>
    </div>
    
    <div class="col-md-3 mt-3">
        <a href="{{ route('admin.settings.roles') }}" class="card text-decoration-none h-100" style="border-color:rgba(var(--bs-theme-rgb),.2);">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <i class="bi bi-shield-check fs-2 text-theme"></i>
                <div>
                    <div class="fw-semibold text-inverse">Role Capabilities</div>
                    <div class="fs-12px text-muted">Configure access limits</div>
                </div>
            </div>
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
        </a>
    </div>
</div>
@endsection
