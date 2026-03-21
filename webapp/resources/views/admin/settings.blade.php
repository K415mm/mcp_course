@extends('layouts.app')

@section('title', 'Global Settings')

@section('content')
    <!-- Content Header -->
    <div class="d-flex align-items-center mb-3">
        <div>
            <h1 class="page-header mb-0">System Settings</h1>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center">
                    <i class="bi bi-display me-2 text-theme"></i> 
                    <span class="fw-semibold">User Interface Constraints</span>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.update') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Cinematic GSAP Animations</label>
                            <p class="text-muted small mb-3">
                                When enabled, the platform will utilize GSAP (GreenSock) to render cinematic UI effects like 
                                the FastMCP plane flyover, fading radar networks, and glitching malware backgrounds. 
                                Disabling this will present a cleaner, static UI for lower-end devices or professional preference.
                            </p>
                            
                            <div class="form-check form-switch px-0 pt-1">
                                <label class="d-flex align-items-center mb-0 cursor-pointer" for="cinematicToggle">
                                    <div style="flex: 1;">
                                        <div class="fw-semibold text-inverse">Enable Cinematic UX</div>
                                    </div>
                                    <div class="flex-shrink-0 ms-3">
                                        <input type="checkbox" class="form-check-input ms-0" style="width: 3rem; height: 1.5rem;" 
                                            id="cinematicToggle" name="cinematic_animations_enabled" value="true" 
                                            {{ $cinematicAnimationsEnabled ? 'checked' : '' }}>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <hr class="border-secondary mb-3">
                        
                        <div class="text-end">
                            <button type="submit" class="btn btn-theme px-4">
                                <i class="bi bi-save2 me-2"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
            </div>
        </div>
    </div>
@endsection