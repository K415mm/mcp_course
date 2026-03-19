@extends('layouts.app')
@section('title', $diagram->title . ' — Diagram Viewer')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('diagrams.index') }}" class="text-theme">Diagrams</a></li>
                @if($diagram->module_slug)
                    <li class="breadcrumb-item">
                        <a href="{{ route('course.module', $diagram->module_slug) }}" class="text-theme">{{ $diagram->module_slug }}</a>
                    </li>
                @endif
                <li class="breadcrumb-item active">{{ $diagram->title }}</li>
            </ol>
        </nav>
        <h1 class="page-header mb-0"><i class="bi bi-bezier2 me-2 text-theme"></i>{{ $diagram->title }}</h1>
    </div>
    @if(Auth::id() === $diagram->user_id)
        <div class="d-flex gap-2">
            <a href="{{ route('diagrams.edit', $diagram->id) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <form method="POST" action="{{ route('diagrams.destroy', $diagram->id) }}" onsubmit="return confirm('Delete this diagram?')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash3"></i></button>
            </form>
        </div>
    @endif
</div>

<div class="card" style="height: calc(100vh - 220px); overflow:hidden;">
    @if($diagram->hasContent())
        {{-- Read-only viewer: embed.diagrams.net with lightbox=1 and load via PostMessage --}}
        <iframe id="drawio-view"
                src="https://embed.diagrams.net/?embed=1&proto=json&ui=dark&lang=en&spin=1&nav=1&lightbox=1"
                style="width:100%;height:100%;border:none;"></iframe>
    @else
        <div class="d-flex align-items-center justify-content-center h-100">
            <div class="text-center text-muted">
                <i class="bi bi-diagram-3" style="font-size:3rem;opacity:.3;"></i>
                <p class="mt-3">No diagram content yet.</p>
            </div>
        </div>
    @endif
    <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
</div>
@endsection

@push('scripts')
@if($diagram->hasContent())
<script>
(function() {
    const frame = document.getElementById('drawio-view');
    const fileUrl = "{{ route('diagrams.file', $diagram->id) }}";

    window.addEventListener('message', function(evt) {
        if (evt.source !== frame.contentWindow) return;
        let msg;
        try { msg = JSON.parse(evt.data); } catch(e) { return; }

        if (msg.event === 'init') {
            // Fetch the .drawio file from our server and load it into the viewer
            fetch(fileUrl, { headers: { 'Accept': 'application/xml, text/xml' } })
                .then(r => r.text())
                .then(xml => {
                    frame.contentWindow.postMessage(JSON.stringify({
                        action: 'load',
                        xml: xml,
                        autosave: 0
                    }), '*');
                });
        }
    });
})();
</script>
@endif
@endpush
