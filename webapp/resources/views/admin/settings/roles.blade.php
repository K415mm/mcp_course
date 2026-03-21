@extends('layouts.app')

@section('title', 'Role Capabilities')

@section('content')
<div class="d-flex align-items-center mb-3">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-theme">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}" class="text-theme">Settings</a></li>
                <li class="breadcrumb-item active">Role Capabilities</li>
            </ol>
        </nav>
        <h1 class="page-header mb-0"><i class="bi bi-shield-check me-2 text-theme"></i>Role Capabilities</h1>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('admin.settings.updateRoles') }}" method="POST">
    @csrf
    <div class="row g-4 mb-4">
        @foreach($roles as $role)
            @php $caps = $roleSettings[$role]; @endphp
            <div class="col-md-4">
                <div class="card h-100 bg-dark border-secondary">
                    <div class="card-header bg-transparent border-bottom border-secondary d-flex align-items-center">
                        <i class="bi bi-person-badge fs-3 me-3 text-secondary"></i> 
                        <h5 class="mb-0 text-uppercase" style="letter-spacing:1px;">{{ $role }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label text-inverse fw-bold px-1 mb-1">Max Enrolled Courses</label>
                            <p class="text-muted small px-1 mb-2">Use -1 for unlimited.</p>
                            <input type="number" name="caps[{{ $role }}][max_courses]" class="form-control form-control-lg bg-dark border-secondary text-inverse fw-bold" value="{{ $caps['max_courses'] ?? 0 }}">
                        </div>
                        
                        <div class="mb-0">
                            <div class="form-check form-switch px-0">
                                <label class="d-flex align-items-center mb-0 cursor-pointer" for="ws_{$role}">
                                    <div style="flex: 1;">
                                        <div class="fw-bold text-inverse fs-15px">Enable Workshops Tab</div>
                                        <div class="text-muted small">Can this role access sandbox workshops?</div>
                                    </div>
                                    <div class="flex-shrink-0 ms-3">
                                        <input type="checkbox" class="form-check-input ms-0" style="width: 3rem; height: 1.5rem;" 
                                            id="ws_{$role}" name="caps[{{ $role }}][workshops_enabled]" value="1" 
                                            {{ !empty($caps['workshops_enabled']) ? 'checked' : '' }}>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                    </div>
                    <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                </div>
            </div>
        @endforeach
    </div>
    
    <div class="text-end mb-5">
        <button type="submit" class="btn btn-theme px-4 py-2 fs-15px fw-bold">
            <i class="bi bi-save2 me-2"></i> Save Changes
        </button>
    </div>
</form>
@endsection
