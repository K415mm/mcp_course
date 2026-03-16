@extends('layouts.app')

@section('title', 'My Notes — ' . config('course.title'))

@section('content')

{{-- ── Sidebar + Editor Layout ── --}}
<div class="row" style="min-height: calc(100vh - 200px);">

    {{-- ── Left sidebar: Note List ── --}}
    <div class="col-xl-3 col-lg-4 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-semibold mb-0"><i class="bi bi-journal-text me-2 text-theme"></i>My Notes</h5>
            <button class="btn btn-sm btn-theme px-3" id="btn-new-note">
                <i class="bi bi-plus-lg me-1"></i>New
            </button>
        </div>

        <div id="note-list" class="d-flex flex-column gap-2">
            @forelse($notes as $n)
                <div class="note-list-item card p-2 px-3 cursor-pointer {{ isset($activeNote) && $activeNote->id === $n->id ? 'border-theme' : '' }}"
                     data-id="{{ $n->id }}" onclick="loadNote({{ $n->id }})">
                    <div class="fw-semibold fs-13px text-inverse text-truncate">{{ $n->title }}</div>
                    <div class="text-muted fs-11px">{{ $n->updated_at->diffForHumans() }}</div>
                    @if($n->module_slug)
                        <span class="badge bg-dark text-muted mt-1" style="font-size:10px;">{{ $n->module_slug }}</span>
                    @endif
                </div>
            @empty
                <div class="text-muted fs-12px text-center py-4">No notes yet. Click "New" to start.</div>
            @endforelse
        </div>
    </div>

    {{-- ── Main Editor ── --}}
    <div class="col-xl-9 col-lg-8">
        <div class="card h-100" id="note-editor-card" style="{{ $notes->isEmpty() ? 'display:none;' : '' }}">
            <div class="card-header d-flex align-items-center justify-content-between py-2 px-4">
                <input type="text" id="note-title" class="form-control form-control-sm bg-transparent border-0 fw-bold fs-5 text-inverse px-0"
                       placeholder="Note Title..." style="max-width: 500px; outline: none; box-shadow: none;">
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

            {{-- TipTap toolbar --}}
            <div class="card-body p-0 d-flex flex-column">
                <div id="editor-toolbar" class="px-3 py-2 border-bottom border-secondary d-flex flex-wrap gap-1">
                    <button class="btn btn-xs btn-dark" data-action="bold" title="Bold"><i class="bi bi-type-bold"></i></button>
                    <button class="btn btn-xs btn-dark" data-action="italic" title="Italic"><i class="bi bi-type-italic"></i></button>
                    <button class="btn btn-xs btn-dark" data-action="strike" title="Strikethrough"><i class="bi bi-type-strikethrough"></i></button>
                    <span class="border-start border-secondary mx-1"></span>
                    <button class="btn btn-xs btn-dark" data-action="h1" title="Heading 1"><b>H1</b></button>
                    <button class="btn btn-xs btn-dark" data-action="h2" title="Heading 2"><b>H2</b></button>
                    <button class="btn btn-xs btn-dark" data-action="h3" title="Heading 3"><b>H3</b></button>
                    <span class="border-start border-secondary mx-1"></span>
                    <button class="btn btn-xs btn-dark" data-action="bulletList" title="Bullet List"><i class="bi bi-list-ul"></i></button>
                    <button class="btn btn-xs btn-dark" data-action="orderedList" title="Numbered List"><i class="bi bi-list-ol"></i></button>
                    <span class="border-start border-secondary mx-1"></span>
                    <button class="btn btn-xs btn-dark" data-action="blockquote" title="Blockquote"><i class="bi bi-quote"></i></button>
                    <button class="btn btn-xs btn-dark" data-action="codeBlock" title="Code Block"><i class="bi bi-code-slash"></i></button>
                    <button class="btn btn-xs btn-dark" data-action="table" title="Insert Table"><i class="bi bi-table"></i></button>
                    <span class="border-start border-secondary mx-1"></span>
                    <button class="btn btn-xs btn-dark" data-action="horizontalRule" title="Horizontal Rule">—</button>
                    <button class="btn btn-xs btn-dark" data-action="undo" title="Undo"><i class="bi bi-arrow-counterclockwise"></i></button>
                    <button class="btn btn-xs btn-dark" data-action="redo" title="Redo"><i class="bi bi-arrow-clockwise"></i></button>
                </div>

                {{-- TipTap editor area --}}
                <div id="tiptap-editor" class="p-4 flex-grow-1"
                     style="min-height: 500px; outline: none; font-size: 15px; line-height: 1.8; color: var(--bs-body-color);"></div>
            </div>

            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
        </div>

        <div id="note-empty-state" class="text-center py-5 {{ !$notes->isEmpty() ? 'display:none;' : '' }}">
            <i class="bi bi-journal-plus text-muted" style="font-size: 4rem; opacity:.3;"></i>
            <p class="text-muted mt-3">Create your first note to get started.</p>
            <button class="btn btn-theme px-4" id="btn-new-note-2"><i class="bi bi-plus-circle me-2"></i>New Note</button>
        </div>
    </div>
</div>

<input type="hidden" id="current-note-id" value="{{ isset($activeNote) ? $activeNote->id : '' }}">
<input type="hidden" id="csrf-token" value="{{ csrf_token() }}">
<input type="hidden" id="initial-body" value="{{ isset($activeNote) ? htmlspecialchars($activeNote->body, ENT_QUOTES) : '' }}">

@endsection

@push('scripts')

{{-- TipTap via CDN (standalone UMD build) --}}
<script src="https://cdn.jsdelivr.net/npm/@tiptap/core@2.2.4/dist/index.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/starter-kit@2.2.4/dist/index.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-table@2.2.4/dist/index.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-table-row@2.2.4/dist/index.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-table-cell@2.2.4/dist/index.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-table-header@2.2.4/dist/index.umd.js"></script>

<style>
    .btn-xs { padding: 2px 8px; font-size: 12px; }
    #tiptap-editor .ProseMirror { outline: none; min-height: 400px; }
    #tiptap-editor .ProseMirror p.is-editor-empty:first-child::before {
        content: attr(data-placeholder);
        float: left;
        color: rgba(255,255,255,0.25);
        pointer-events: none;
        height: 0;
    }
    #tiptap-editor h1 { font-size: 1.8rem; font-weight: 700; margin-bottom: .5rem; }
    #tiptap-editor h2 { font-size: 1.4rem; font-weight: 600; margin-bottom: .4rem; }
    #tiptap-editor h3 { font-size: 1.1rem; font-weight: 600; margin-bottom: .3rem; }
    #tiptap-editor blockquote { border-left: 4px solid var(--bs-theme); padding-left: 1rem; color: rgba(255,255,255,.6); }
    #tiptap-editor pre { background: rgba(0,0,0,.5); border-radius: 8px; padding: 1rem; }
    #tiptap-editor code { background: rgba(255,255,255,.08); border-radius: 4px; padding: 1px 6px; }
    #tiptap-editor table { border-collapse: collapse; width: 100%; }
    #tiptap-editor td, #tiptap-editor th { border: 1px solid rgba(255,255,255,.15); padding: 8px; }
    #tiptap-editor th { background: rgba(255,255,255,.05); }
    .note-list-item { cursor: pointer; transition: border-color .2s; }
    .note-list-item:hover { border-color: rgba(var(--bs-theme-rgb),.5) !important; }
</style>

<script>
const { Editor } = window['@tiptap/core'];
const StarterKit = window['@tiptap/starter-kit'].StarterKit;
const Table = window['@tiptap/extension-table'].Table;
const TableRow = window['@tiptap/extension-table-row'].TableRow;
const TableCell = window['@tiptap/extension-table-cell'].TableCell;
const TableHeader = window['@tiptap/extension-table-header'].TableHeader;

let editor = null;
let currentNoteId = document.getElementById('current-note-id').value;
let saveTimer = null;
const csrfToken = document.getElementById('csrf-token').value;

// ── Initialize TipTap Editor ──────────────────────────────────────────────────
function initEditor(initialContent = '') {
    if (editor) { editor.destroy(); }
    editor = new Editor({
        element: document.getElementById('tiptap-editor'),
        extensions: [
            StarterKit,
            Table.configure({ resizable: true }),
            TableRow, TableHeader, TableCell,
        ],
        content: initialContent ? JSON.parse(initialContent) : '',
        onUpdate: () => {
            document.getElementById('save-status').textContent = 'Saving...';
            clearTimeout(saveTimer);
            saveTimer = setTimeout(autoSave, 1200);
        }
    });
}

// ── Auto-Save to Server ───────────────────────────────────────────────────────
async function autoSave() {
    if (!currentNoteId || !editor) return;
    const body = JSON.stringify(editor.getJSON());
    const bodyMarkdown = getTipTapMarkdown();
    const title = document.getElementById('note-title').value;

    const res = await fetch(`/notes/${currentNoteId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ title, body, body_markdown: bodyMarkdown })
    });
    if (res.ok) {
        const data = await res.json();
        document.getElementById('save-status').textContent = `Saved ${data.updated_at}`;
    }
}

// ── Simple HTML → Markdown Converter ─────────────────────────────────────────
function getTipTapMarkdown() {
    const el = document.getElementById('tiptap-editor');
    let html = el.innerHTML;
    return html
        .replace(/<h1[^>]*>(.*?)<\/h1>/gi, '# $1\n\n')
        .replace(/<h2[^>]*>(.*?)<\/h2>/gi, '## $1\n\n')
        .replace(/<h3[^>]*>(.*?)<\/h3>/gi, '### $1\n\n')
        .replace(/<strong>(.*?)<\/strong>/gi, '**$1**')
        .replace(/<em>(.*?)<\/em>/gi, '*$1*')
        .replace(/<code>(.*?)<\/code>/gi, '`$1`')
        .replace(/<blockquote[^>]*>(.*?)<\/blockquote>/gis, '> $1\n')
        .replace(/<li[^>]*>(.*?)<\/li>/gi, '- $1\n')
        .replace(/<p[^>]*>(.*?)<\/p>/gi, '$1\n\n')
        .replace(/<br\s*\/?>/gi, '\n')
        .replace(/<[^>]+>/g, '');
}

// ── Load a Note from the List ─────────────────────────────────────────────────
async function loadNote(noteId) {
    const res = await fetch(`/notes/view/${noteId}`);
    if (!res.ok) return;
    // Redirect to note URL
    window.location.href = `/notes/view/${noteId}`;
}

// ── Toolbar Button Actions ────────────────────────────────────────────────────
document.querySelectorAll('[data-action]').forEach(btn => {
    btn.addEventListener('click', () => {
        if (!editor) return;
        const action = btn.dataset.action;
        switch(action) {
            case 'bold': editor.chain().focus().toggleBold().run(); break;
            case 'italic': editor.chain().focus().toggleItalic().run(); break;
            case 'strike': editor.chain().focus().toggleStrike().run(); break;
            case 'h1': editor.chain().focus().toggleHeading({ level: 1 }).run(); break;
            case 'h2': editor.chain().focus().toggleHeading({ level: 2 }).run(); break;
            case 'h3': editor.chain().focus().toggleHeading({ level: 3 }).run(); break;
            case 'bulletList': editor.chain().focus().toggleBulletList().run(); break;
            case 'orderedList': editor.chain().focus().toggleOrderedList().run(); break;
            case 'blockquote': editor.chain().focus().toggleBlockquote().run(); break;
            case 'codeBlock': editor.chain().focus().toggleCodeBlock().run(); break;
            case 'horizontalRule': editor.chain().focus().setHorizontalRule().run(); break;
            case 'table': editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run(); break;
            case 'undo': editor.chain().focus().undo().run(); break;
            case 'redo': editor.chain().focus().redo().run(); break;
        }
    });
});

// ── Create New Note ────────────────────────────────────────────────────────────
async function createNote() {
    const res = await fetch('/notes/create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ title: 'Untitled Note' })
    });
    if (res.ok) {
        const data = await res.json();
        window.location.href = `/notes/view/${data.id}`;
    }
}
document.getElementById('btn-new-note').addEventListener('click', createNote);
document.getElementById('btn-new-note-2')?.addEventListener('click', createNote);

// ── Export Markdown ───────────────────────────────────────────────────────────
document.getElementById('btn-export-md').addEventListener('click', () => {
    const md = getTipTapMarkdown();
    const title = document.getElementById('note-title').value || 'note';
    const blob = new Blob([md], { type: 'text/markdown' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `${title.replace(/\s+/g, '_')}.md`;
    a.click();
});

// ── Delete Note ──────────────────────────────────────────────────────────────
document.getElementById('btn-delete-note').addEventListener('click', async () => {
    if (!currentNoteId || !confirm('Delete this note permanently?')) return;
    const res = await fetch(`/notes/${currentNoteId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken }
    });
    if (res.ok) window.location.href = '/notes';
});

// ── Title auto-save ────────────────────────────────────────────────────────────────
document.getElementById('note-title').addEventListener('input', () => {
    clearTimeout(saveTimer);
    saveTimer = setTimeout(autoSave, 1200);
});

// ── Init ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    if (currentNoteId) {
        const initialBody = document.getElementById('initial-body').value;
        document.getElementById('note-editor-card').style.display = '';
        document.getElementById('note-empty-state').style.display = 'none';
        initEditor(initialBody);

        // Set the title field
        const activeItem = document.querySelector(`.note-list-item[data-id="${currentNoteId}"]`);
        if (activeItem) {
            document.getElementById('note-title').value = activeItem.querySelector('.fw-semibold').textContent;
        }
    }
});
</script>
@endpush
