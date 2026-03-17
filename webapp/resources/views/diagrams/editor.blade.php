@extends('layouts.app')
@section('title', isset($diagram) ? 'Edit — '.$diagram->title : 'New Diagram')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h1 class="page-header mb-0">
            <i class="bi bi-pencil-square me-2 text-theme"></i>
            {{ isset($diagram) ? 'Edit Diagram' : 'New Diagram' }}
        </h1>
    </div>
    <div class="d-flex gap-2">
        <input type="text" id="diagram-title" class="form-control form-control-sm bg-dark border-secondary text-inverse"
               value="{{ $diagram->title ?? 'Untitled Diagram' }}" placeholder="Diagram title…" style="max-width:280px;">
        <select id="diagram-module" class="form-select form-select-sm bg-dark border-secondary text-inverse" style="max-width:220px;">
            <option value="">— No module —</option>
            @if(isset($allItems))
                @foreach($allItems as $item)
                    <option value="{{ $item['slug'] }}" {{ isset($diagram) && $diagram->module_slug === $item['slug'] ? 'selected' : '' }}>
                        {{ $item['title'] }}
                    </option>
                @endforeach
            @endif
        </select>
        <button id="btn-save" class="btn btn-theme px-4"><i class="bi bi-floppy me-2"></i>Save</button>
        <a href="{{ route('diagrams.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</div>

<div id="save-status" class="text-muted fs-11px mb-2"></div>

{{-- draw.io embedded editor --}}
<div class="card" style="height: calc(100vh - 210px); overflow: hidden;">
    <iframe id="drawio-frame"
            src="https://embed.diagrams.net/?embed=1&spin=1&modified=unsavedChanges&proto=json&ui=dark&lang=en"
            style="width:100%;height:100%;border:none;"></iframe>
    <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
</div>

<input type="hidden" id="diagram-id" value="{{ $diagram->id ?? '' }}">
<input type="hidden" id="csrf-token" value="{{ csrf_token() }}">
<input type="hidden" id="initial-xml" value="{{ isset($diagram) ? htmlspecialchars($diagram->xml_data ?? '', ENT_QUOTES) : '' }}">
@endsection

@push('scripts')
<script>
(function () {
    const frame = document.getElementById('drawio-frame');
    const diagramId = document.getElementById('diagram-id').value;
    const csrf = document.getElementById('csrf-token').value;
    let initialized = false;

    // ── Handle messages from draw.io iframe ──────────────────────
    window.addEventListener('message', function (evt) {
        if (evt.source !== frame.contentWindow) return;

        let msg;
        try { msg = JSON.parse(evt.data); } catch (e) { return; }

        switch (msg.event) {
            case 'init':
                // draw.io is ready — load existing XML or start fresh
                initialized = true;
                const xmlVal = document.getElementById('initial-xml').value;
                if (xmlVal) {
                    postToDrawio({ action: 'load', xml: xmlVal, autosave: 1 });
                } else {
                    postToDrawio({ action: 'load', xml: '<mxGraphModel><root><mxCell id="0"/><mxCell id="1" parent="0"/></root></mxGraphModel>', autosave: 1 });
                }
                break;

            case 'autosave':
                // Auto-triggered on every change — save silently
                if (msg.xml) autoSave(msg.xml);
                break;

            case 'save':
                // Explicit Ctrl+S
                if (msg.xml) autoSave(msg.xml, true);
                break;

            case 'export':
                // The requested export XML comes here
                if (msg.data) {
                    autoSave(msg.data, true);
                } else if (msg.xml) {
                    autoSave(msg.xml, true);
                }
                break;
        }
    });

    function postToDrawio(payload) {
        frame.contentWindow.postMessage(JSON.stringify(payload), '*');
    }

    // ── Save to Server ────────────────────────────────────────────
    async function autoSave(xml, explicit = false) {
        const title = document.getElementById('diagram-title').value || 'Untitled';
        const moduleSlug = document.getElementById('diagram-module').value;
        const status = document.getElementById('save-status');
        status.textContent = 'Saving…';

        if (diagramId) {
            // UPDATE existing
            const res = await fetch(`/diagrams/${diagramId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ title, xml_data: xml, module_slug: moduleSlug })
            });
            if (res.ok) {
                const d = await res.json();
                status.textContent = `Saved ${d.updated_at}`;
            }
        } else {
            // CREATE new on first save
            const res = await fetch('/diagrams', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ title, xml_data: xml, module_slug: moduleSlug })
            });
            if (res.ok) {
                const d = await res.json();
                // Redirect to edit URL (so subsequent saves use PUT)
                window.history.replaceState({}, '', `/diagrams/${d.id}/edit`);
                document.getElementById('diagram-id').value = d.id;
                status.textContent = 'Created & saved!';
            }
        }
    }

    // Save button
    document.getElementById('btn-save').addEventListener('click', () => {
        postToDrawio({ action: 'export', format: 'xmlxml' });
        // Fallback: request current XML via save action
        postToDrawio({ action: 'save' });
    });
})();
</script>
@endpush
