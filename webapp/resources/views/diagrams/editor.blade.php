@extends('layouts.app')
@section('title', isset($diagram) ? 'Edit — '.$diagram->title : 'New Diagram')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h1 class="page-header mb-0">
            <i class="bi bi-pencil-square me-2 text-theme"></i>
            {{ isset($diagram) ? 'Edit: '.$diagram->title : 'New Diagram' }}
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
@endsection

@push('scripts')
<script>
(function () {
    const frame = document.getElementById('drawio-frame');
    let diagramId = document.getElementById('diagram-id').value;
    const csrf = document.getElementById('csrf-token').value;

    // ── Load existing XML from server if we have a diagram ID ─────────
    @if(isset($xmlContent) && $xmlContent)
    const initialXml = {!! json_encode($xmlContent) !!};
    @else
    const initialXml = null;
    @endif

    // ── Handle messages from draw.io iframe ──────────────────────────
    window.addEventListener('message', function (evt) {
        if (evt.source !== frame.contentWindow) return;

        let msg;
        try { msg = JSON.parse(evt.data); } catch (e) { return; }

        switch (msg.event) {
            case 'init':
                // draw.io is ready — load existing XML or start fresh
                if (initialXml) {
                    postToDrawio({ action: 'load', xml: initialXml, autosave: 1 });
                } else {
                    postToDrawio({ action: 'load', xml: '<mxGraphModel><root><mxCell id="0"/><mxCell id="1" parent="0"/></root></mxGraphModel>', autosave: 1 });
                }
                break;

            case 'autosave':
            case 'save':
                if (msg.xml) autoSave(msg.xml);
                break;

            case 'export':
                const xmlData = msg.xml || msg.data;
                if (xmlData) autoSave(xmlData);
                break;
        }
    });

    function postToDrawio(payload) {
        frame.contentWindow.postMessage(JSON.stringify(payload), '*');
    }

    // ── Save XML to Server as a .drawio file ─────────────────────────
    async function autoSave(xml) {
        const title = document.getElementById('diagram-title').value || 'Untitled';
        const moduleSlug = document.getElementById('diagram-module').value;
        const status = document.getElementById('save-status');
        status.textContent = 'Saving…';

        const url = diagramId ? `/diagrams/${diagramId}` : '/diagrams';
        const method = diagramId ? 'PUT' : 'POST';

        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ title, xml_data: xml, module_slug: moduleSlug })
        });

        if (res.ok) {
            const d = await res.json();
            if (!diagramId && d.id) {
                diagramId = d.id;
                document.getElementById('diagram-id').value = d.id;
                window.history.replaceState({}, '', `/diagrams/${d.id}/edit`);
            }
            status.textContent = d.updated_at ? `Saved ${d.updated_at}` : 'Saved!';
        } else {
            status.textContent = 'Save failed. Please try again.';
        }
    }

    // Save button triggers diagram XML export
    document.getElementById('btn-save').addEventListener('click', () => {
        postToDrawio({ action: 'export', format: 'xml' });
    });
})();
</script>
@endpush
