{{--
    Diagram Embed Partial
    Displays a Draw.io diagram inline inside a lesson or module page.
    Expects: $embeddedDiagram (Diagram model instance)
--}}
@if(isset($embeddedDiagram) && $embeddedDiagram && $embeddedDiagram->hasContent())
<div class="card mb-4 diagram-embed-card" style="border-color:rgba(var(--bs-theme-rgb),.2);">
    <div class="card-header bg-transparent d-flex align-items-center gap-2 py-2">
        <i class="bi bi-bezier2 text-theme"></i>
        <span class="fw-semibold fs-13px text-inverse">{{ $embeddedDiagram->title }}</span>
        <a href="{{ route('diagrams.show', $embeddedDiagram->id) }}"
           class="btn btn-sm btn-link text-theme ms-auto p-0 fs-12px"
           target="_blank" title="Open full view">
            <i class="bi bi-box-arrow-up-right"></i> Full View
        </a>
    </div>
    <div class="card-body p-0" style="height:480px; overflow:hidden; position:relative;">
        <iframe id="diagram-embed-{{ $embeddedDiagram->id }}"
                src="https://embed.diagrams.net/?embed=1&proto=json&ui=dark&spin=1&nav=1"
                style="width:100%;height:100%;border:none;"></iframe>
        <div class="diagram-loading-overlay" id="diagram-loading-{{ $embeddedDiagram->id }}"
             style="position:absolute;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;z-index:10;">
            <div class="text-center text-muted">
                <div class="spinner-border spinner-border-sm text-theme mb-2"></div>
                <p class="fs-12px mb-0">Loading diagram…</p>
            </div>
        </div>
    </div>
    <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
</div>

@push('scripts')
<script>
(function() {
    const frame = document.getElementById('diagram-embed-{{ $embeddedDiagram->id }}');
    const overlay = document.getElementById('diagram-loading-{{ $embeddedDiagram->id }}');
    const fileUrl = "{{ route('diagrams.file', $embeddedDiagram->id) }}";

    window.addEventListener('message', function(evt) {
        if (evt.source !== frame.contentWindow) return;
        let msg;
        try { msg = JSON.parse(evt.data); } catch(e) { return; }

        if (msg.event === 'init') {
            fetch(fileUrl, { headers: { 'Accept': 'application/xml' } })
                .then(r => r.text())
                .then(xml => {
                    frame.contentWindow.postMessage(JSON.stringify({
                        action: 'load',
                        xml: xml,
                        autosave: 0
                    }), '*');
                    // Hide loading overlay
                    overlay.style.display = 'none';
                });
        }
    });
})();
</script>
@endpush
@endif
