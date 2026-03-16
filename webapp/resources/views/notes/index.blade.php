@extends('layouts.app')

@section('title', 'My Notes — ' . config('course.title'))

@section('content')

<div class="row" style="min-height: calc(100vh - 200px);">

    {{-- ── Left sidebar: Note List ──────────────────────────────── --}}
    <div class="col-xl-3 col-lg-4 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-semibold mb-0"><i class="bi bi-journal-text me-2 text-theme"></i>My Notes</h5>
            <button class="btn btn-sm btn-theme px-3" id="btn-new-note">
                <i class="bi bi-plus-lg me-1"></i>New
            </button>
        </div>

        <div id="note-list" class="d-flex flex-column gap-2">
            @forelse($notes as $n)
                <a href="{{ route('notes.show', $n->id) }}" class="text-decoration-none">
                    <div class="note-list-item card p-2 px-3 {{ isset($activeNote) && $activeNote->id === $n->id ? 'border-theme' : '' }}">
                        <div class="fw-semibold fs-13px text-inverse text-truncate">{{ $n->title }}</div>
                        <div class="text-muted fs-11px">{{ $n->updated_at->diffForHumans() }}</div>
                        @if($n->module_slug)
                            <span class="badge bg-dark text-muted mt-1" style="font-size:10px;">{{ $n->module_slug }}</span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="text-muted fs-12px text-center py-4">No notes yet. Click "New" to start.</div>
            @endforelse
        </div>
    </div>

    {{-- ── Main Editor ──────────────────────────────────────────── --}}
    <div class="col-xl-9 col-lg-8">
        @if(isset($activeNote))
            <div class="card h-100" id="note-editor-card">
                <div class="card-header d-flex align-items-center justify-content-between py-2 px-4">
                    <input type="text" id="note-title" class="form-control form-control-sm bg-transparent border-0 fw-bold text-inverse px-0"
                           value="{{ $activeNote->title }}" placeholder="Note Title…"
                           style="font-size:1.2rem; outline:none; box-shadow:none; max-width:480px;">
                    <div class="d-flex align-items-center gap-2">
                        <span id="save-status" class="text-muted fs-11px me-2">All changes saved</span>
                        <button class="btn btn-sm btn-outline-success" id="btn-export-md">
                            <i class="bi bi-markdown me-1"></i>Export .md
                        </button>
                        <button class="btn btn-sm btn-outline-danger" id="btn-delete-note">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </div>
                </div>

                {{-- Quill custom toolbar --}}
                <div id="quill-toolbar" class="px-3 py-2 border-bottom border-secondary d-flex flex-wrap gap-1 align-items-center" style="background: rgba(0,0,0,.2);">
                    <button class="ql-bold" title="Bold"><i class="bi bi-type-bold"></i></button>
                    <button class="ql-italic" title="Italic"><i class="bi bi-type-italic"></i></button>
                    <button class="ql-underline" title="Underline"><i class="bi bi-type-underline"></i></button>
                    <button class="ql-strike" title="Strike"><i class="bi bi-type-strikethrough"></i></button>
                    <span class="ql-sep"></span>
                    <select class="ql-header">
                        <option value="1">H1</option>
                        <option value="2">H2</option>
                        <option value="3">H3</option>
                        <option selected></option>
                    </select>
                    <span class="ql-sep"></span>
                    <button class="ql-list" value="bullet"><i class="bi bi-list-ul"></i></button>
                    <button class="ql-list" value="ordered"><i class="bi bi-list-ol"></i></button>
                    <button class="ql-blockquote"><i class="bi bi-quote"></i></button>
                    <button class="ql-code-block"><i class="bi bi-code-slash"></i></button>
                    <span class="ql-sep"></span>
                    <button class="ql-link"><i class="bi bi-link-45deg"></i></button>
                    <button class="ql-clean"><i class="bi bi-eraser"></i></button>
                </div>

                {{-- Quill editor surface --}}
                <div id="quill-editor" style="flex: 1; font-size:15px; min-height:480px;"></div>

                <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-journal-plus text-muted" style="font-size:4rem;opacity:.3;"></i>
                <p class="text-muted mt-3">Create your first note to get started.</p>
                <button class="btn btn-theme px-4" id="btn-new-note-alt"><i class="bi bi-plus-circle me-2"></i>New Note</button>
            </div>
        @endif
    </div>
</div>

<input type="hidden" id="current-note-id" value="{{ isset($activeNote) ? $activeNote->id : '' }}">
<input type="hidden" id="csrf-token" value="{{ csrf_token() }}">

@endsection

@push('scripts')
{{-- Quill.js — stable CDN with proper globals --}}
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>

<style>
    /* Dark-mode Quill overrides */
    .ql-container { border: none !important; }
    .ql-editor { color: rgba(255,255,255,.87) !important; font-size: 15px; line-height: 1.8; min-height: 480px; }
    .ql-editor.ql-blank::before { color: rgba(255,255,255,.25) !important; font-style: normal; }
    .ql-toolbar { display: none; } /* Hide default toolbar — we use custom */
    #quill-toolbar button, #quill-toolbar select {
        background: transparent; border: 1px solid rgba(255,255,255,.1);
        color: rgba(255,255,255,.7); border-radius:4px; padding: 2px 8px; cursor: pointer; font-size:13px;
    }
    #quill-toolbar button:hover, #quill-toolbar select:hover { background: rgba(255,255,255,.08); color: #fff; }
    .ql-sep { display: inline-block; width: 1px; height: 20px; background: rgba(255,255,255,.15); margin: 0 4px; }
    .ql-snow .ql-editor pre.ql-syntax { background: rgba(0,0,0,.5); border-radius: 6px; color: #7dd3fc; }
    .ql-snow .ql-editor blockquote { border-left: 4px solid var(--bs-theme); color: rgba(255,255,255,.6); }
    .ql-snow a { color: var(--bs-theme); }
    .note-list-item { cursor:pointer; transition: border-color .2s; }
    .note-list-item:hover { border-color: rgba(var(--bs-theme-rgb),.5) !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const noteId   = document.getElementById('current-note-id').value;
    const csrf     = document.getElementById('csrf-token').value;
    let saveTimer  = null;
    let quill      = null;

    // ── Init Quill ────────────────────────────────────────────────
    if (noteId) {
        quill = new Quill('#quill-editor', {
            theme: 'snow',
            placeholder: 'Start writing your note here…',
            modules: {
                toolbar: '#quill-toolbar', // bind our custom toolbar
                history: { delay: 1000, maxStack: 100, userOnly: true },
            }
        });

        // Load saved body (HTML string)
        const savedBody = {!! isset($activeNote) && $activeNote->body ? json_encode($activeNote->body) : '""' !!};
        if (savedBody) {
            quill.clipboard.dangerouslyPasteHTML(savedBody);
        }

        // Auto-save on change
        quill.on('text-change', () => {
            document.getElementById('save-status').textContent = 'Saving…';
            clearTimeout(saveTimer);
            saveTimer = setTimeout(autoSave, 1200);
        });
    }

    // ── Auto-Save ─────────────────────────────────────────────────
    async function autoSave() {
        if (!noteId || !quill) return;
        const body    = quill.root.innerHTML;
        const title   = document.getElementById('note-title')?.value || 'Untitled';
        const md      = htmlToMd(body);

        const res = await fetch(`/notes/${noteId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ title, body, body_markdown: md })
        });
        if (res.ok) {
            const d = await res.json();
            document.getElementById('save-status').textContent = `Saved ${d.updated_at}`;
        }
    }

    // Title field
    document.getElementById('note-title')?.addEventListener('input', () => {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(autoSave, 1200);
    });

    // ── New Note ──────────────────────────────────────────────────
    async function createNote() {
        const res = await fetch('/notes/create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ title: 'Untitled Note' })
        });
        if (res.ok) {
            const d = await res.json();
            window.location.href = `/notes/view/${d.id}`;
        }
    }
    document.getElementById('btn-new-note')?.addEventListener('click', createNote);
    document.getElementById('btn-new-note-alt')?.addEventListener('click', createNote);

    // ── Export Markdown ───────────────────────────────────────────
    document.getElementById('btn-export-md')?.addEventListener('click', () => {
        if (!quill) return;
        const md    = htmlToMd(quill.root.innerHTML);
        const title = document.getElementById('note-title')?.value || 'note';
        const blob  = new Blob([md], { type: 'text/markdown' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `${title.replace(/\s+/g, '_')}.md`;
        a.click();
    });

    // ── Delete ────────────────────────────────────────────────────
    document.getElementById('btn-delete-note')?.addEventListener('click', async () => {
        if (!noteId || !confirm('Delete this note permanently?')) return;
        const res = await fetch(`/notes/${noteId}`, {
            method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf }
        });
        if (res.ok) window.location.href = '/notes';
    });

    // ── Simple HTML → Markdown ────────────────────────────────────
    function htmlToMd(html) {
        return html
            .replace(/<h1[^>]*>(.*?)<\/h1>/gi, '# $1\n\n')
            .replace(/<h2[^>]*>(.*?)<\/h2>/gi, '## $1\n\n')
            .replace(/<h3[^>]*>(.*?)<\/h3>/gi, '### $1\n\n')
            .replace(/<strong>(.*?)<\/strong>/gi, '**$1**')
            .replace(/<em>(.*?)<\/em>/gi, '*$1*')
            .replace(/<u>(.*?)<\/u>/gi, '__$1__')
            .replace(/<s>(.*?)<\/s>/gi, '~~$1~~')
            .replace(/<code>(.*?)<\/code>/gi, '`$1`')
            .replace(/<blockquote[^>]*>([\s\S]*?)<\/blockquote>/gi, '> $1\n')
            .replace(/<li[^>]*>(.*?)<\/li>/gi, '- $1\n')
            .replace(/<p[^>]*>(.*?)<\/p>/gi, '$1\n\n')
            .replace(/<br\s*\/?>/gi, '\n')
            .replace(/<[^>]+>/g, '')
            .trim();
    }
});
</script>
@endpush
