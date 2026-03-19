@extends('layouts.app')
@section('title', 'My Diagrams')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="page-header mb-0"><i class="bi bi-bezier2 me-2 text-theme"></i>Diagrams</h1>
    @auth
        @if(Auth::user()->isAdmin())
            <a href="{{ route('diagrams.create') }}" class="btn btn-theme px-4">
                <i class="bi bi-plus-circle me-2"></i>New Diagram
            </a>
        @endif
    @endauth
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-3">
    @forelse($diagrams as $diagram)
        <div class="col-xl-4 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    {{-- draw.io thumbnail preview --}}
                    <div class="mb-3 rounded overflow-hidden" style="background: rgba(255,255,255,.04); height:160px; display:flex; align-items:center; justify-content:center;">
                        @if($diagram->xml_data)
                            <img src="{{ $diagram->kroki_url }}"
                                 alt="{{ $diagram->title }}"
                                 style="max-height:150px;max-width:100%;object-fit:contain;"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                            <div style="display:none;align-items:center;justify-content:center;width:100%;height:100%;">
                                <i class="bi bi-diagram-3 text-muted" style="font-size:2.5rem;opacity:.3;"></i>
                            </div>
                        @else
                            <i class="bi bi-diagram-3 text-muted" style="font-size:2.5rem;opacity:.3;"></i>
                        @endif
                    </div>

                    <h6 class="fw-bold text-inverse mb-1">{{ $diagram->title }}</h6>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge {{ $diagram->is_published ? 'bg-success' : 'bg-secondary' }}">
                            {{ $diagram->is_published ? 'Published' : 'Draft' }}
                        </span>
                        @if($diagram->module_slug)
                            <span class="badge bg-dark text-muted fs-11px">{{ $diagram->module_slug }}</span>
                        @endif
                        <span class="text-muted fs-11px ms-auto">{{ $diagram->updated_at->diffForHumans() }}</span>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('diagrams.show', $diagram->id) }}" class="btn btn-sm btn-outline-theme flex-fill">
                            <i class="bi bi-eye me-1"></i>View
                        </a>
                        @if(Auth::id() === $diagram->user_id)
                            <a href="{{ route('diagrams.edit', $diagram->id) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('diagrams.publish', $diagram->id) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $diagram->is_published ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                        title="{{ $diagram->is_published ? 'Unpublish' : 'Publish' }}">
                                    <i class="bi {{ $diagram->is_published ? 'bi-eye-slash' : 'bi-send' }}"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <i class="bi bi-bezier2 text-muted" style="font-size:4rem;opacity:.3;"></i>
            <p class="text-muted mt-3">No diagrams yet.</p>
            @auth
                @if(Auth::user()->isAdmin())
                    <a href="{{ route('diagrams.create') }}" class="btn btn-theme px-4"><i class="bi bi-plus-circle me-2"></i>Create First Diagram</a>
                @endif
            @endauth
        </div>
    @endforelse
</div>
@endsection
