@extends('layouts.app')
@section('title', $diagram->title)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('diagrams.index') }}">Diagrams</a></li>
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
    {{-- Embed draw.io in lightbox/viewer mode (read-only using ?lightbox=1) --}}
    @if($diagram->xml_data)
        <iframe id="drawio-view"
                src="https://embed.diagrams.net/?lightbox=1&highlight=0000ff&edit=_blank&layers=1&nav=1&title={{ urlencode($diagram->title) }}"
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
@if($diagram->xml_data)
<script>
// Load XML into the viewer iframe via PostMessage
const viewFrame = document.getElementById('drawio-view');
window.addEventListener('message', function(evt) {
    if (evt.source !== viewFrame.contentWindow) return;
    let msg;
    try { msg = JSON.parse(evt.data); } catch(e) { return; }
    if (msg.event === 'init') {
        viewFrame.contentWindow.postMessage(JSON.stringify({
            action: 'load',
            xml: {!! json_encode($diagram->xml_data) !!}
        }), '*');
    }
});
</script>
@endif
@endpush
