@extends('layouts.app')

@section('title', isset($path) ? 'Edit Lesson — Admin' : 'New Lesson — Admin')

@push('head')
    <link rel="stylesheet" href="{{ asset('hud/plugins/summernote/dist/summernote-lite.min.css') }}">
    <style>
        .note-editor {
            background: #1a1d23 !important;
            border-color: rgba(255, 255, 255, .1) !important;
        }

        .note-toolbar {
            background: #23262d !important;
            border-bottom-color: rgba(255, 255, 255, .1) !important;
        }

        .note-editable {
            background: #1a1d23 !important;
            color: #e8eaf0 !important;
            min-height: 420px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.9rem;
        }

        .note-statusbar {
            background: #23262d !important;
        }

        .fm-sidebar {
            width: 260px;
            min-height: 100vh;
            border-right: 1px solid rgba(255, 255, 255, .07);
            padding: 1rem;
        }

        .fm-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .25rem .6rem;
            border-radius: .3rem;
            font-size: .72rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .15s;
        }
    </style>
@endpush

@section('content')
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-theme">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.content.index') }}" class="text-theme">Content</a></li>
            <li class="breadcrumb-item active">{{ isset($path) ? 'Edit' : 'New' }}</li>
        </ol>
    </nav>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fs-13px mb-4"><i
                class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close"
                data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row g-4">
        <!-- Left: Front-Matter Panel -->
        <div class="col-xl-3 col-lg-4">
            <div class="card sticky-top" style="top: 70px;">
                <div class="card-header py-2"><span class="fw-semibold fs-13px"><i
                            class="bi bi-sliders me-2 text-theme"></i>Front-Matter</span></div>
                <div class="card-body p-3">
                    <!-- Content Type -->
                    <div class="mb-3">
                        <label class="form-label fs-12px fw-semibold text-muted">Content Type</label>
                        <div id="typeOptions" class="d-flex flex-wrap gap-1">
                            @foreach(['lesson', 'quiz', 'video', 'workshop', 'slides'] as $t)
                                @php $colors = ['lesson' => 'bg-info', 'quiz' => 'bg-warning text-dark', 'video' => 'bg-danger', 'workshop' => 'bg-success', 'slides' => 'bg-primary'] @endphp
                                <span class="fm-badge {{ $colors[$t] }} type-btn {{ $t === 'lesson' ? 'ring' : '' }}"
                                    data-type="{{ $t }}" style="opacity:.6;" onclick="selectType('{{ $t }}')">
                                    {{ $t }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    <!-- Title -->
                    <div class="mb-3">
                        <label class="form-label fs-12px fw-semibold text-muted">Title</label>
                        <input type="text" id="fmTitle" class="form-control form-control-sm bg-inverse bg-opacity-5"
                            placeholder="Lesson title">
                    </div>
                    <!-- Video URL (shown for type=video) -->
                    <div class="mb-3" id="videoField" style="display:none;">
                        <label class="form-label fs-12px fw-semibold text-muted">Video URL</label>
                        <input type="text" id="fmVideo" class="form-control form-control-sm bg-inverse bg-opacity-5"
                            placeholder="YouTube embed or /storage/media/...">
                        <div class="fs-11px text-muted mt-1">Paste a YouTube embed URL or a path from the media library.
                        </div>
                    </div>
                    <!-- Thumbnail (for video) -->
                    <div class="mb-3" id="thumbField" style="display:none;">
                        <label class="form-label fs-12px fw-semibold text-muted">Thumbnail URL</label>
                        <input type="text" id="fmThumb" class="form-control form-control-sm bg-inverse bg-opacity-5"
                            placeholder="/storage/media/thumb.jpg">
                    </div>
                    <button onclick="rebuildFrontmatter()" class="btn btn-outline-theme btn-sm w-100">
                        <i class="bi bi-arrow-repeat me-1"></i>Apply to Content
                    </button>
                </div>
                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>
            </div>
        </div>

        <!-- Right: Editor -->
        <div class="col-xl-9 col-lg-8">
            <div class="card">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="bi bi-{{ isset($path) ? 'pencil' : 'plus-circle' }} text-theme me-2"></i>
                        {{ isset($path) ? 'Edit: ' . ($filename ?? 'lesson') : 'Create New Lesson' }}
                    </h5>

                    @php $isEdit = isset($path); @endphp
                    <form method="POST"
                        action="{{ $isEdit ? route('admin.content.update', [$moduleSlug, $filename]) : route('admin.content.store') }}"
                        id="editorForm">
                        @csrf
                        @if($isEdit) @method('PUT') @endif

                        <div class="row g-3 mb-3">
                            <!-- Module select -->
                            <div class="col-md-5">
                                <label class="form-label fs-12px fw-semibold">Module</label>
                                <select name="module_slug" class="form-select form-select-sm bg-inverse bg-opacity-5" {{ $isEdit ? 'disabled' : '' }}>
                                    @foreach($items as $item)
                                        <option value="{{ $item['slug'] }}" {{ ($item['slug'] === $moduleSlug) ? 'selected' : '' }}>
                                            {{ $item['title'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($isEdit) <input type="hidden" name="module_slug" value="{{ $moduleSlug }}"> @endif
                            </div>
                            <!-- Section select -->
                            <div class="col-md-4">
                                <label class="form-label fs-12px fw-semibold">Section</label>
                                <select name="section" class="form-select form-select-sm bg-inverse bg-opacity-5">
                                    @foreach($sections as $sec)
                                        <option value="{{ $sec }}" {{ $sec === ($section ?? 'theoretical') ? 'selected' : '' }}>
                                            {{ $sec }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Filename (only for new) -->
                            @if(!$isEdit)
                                <div class="col-md-3">
                                    <label class="form-label fs-12px fw-semibold">Filename <span class="text-muted">(no
                                            .md)</span></label>
                                    <input type="text" name="filename"
                                        class="form-control form-control-sm bg-inverse bg-opacity-5 @error('filename') is-invalid @enderror"
                                        placeholder="01_intro_lesson" required pattern="[\w\-]+">
                                    @error('filename')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            @endif
                        </div>

                        <!-- The Summernote editor (raw text mode) -->
                        <textarea id="markdownEditor" name="content" class="d-none">{{ $template ?? '' }}</textarea>
                        <div id="codeEditor" contenteditable="true"
                            style="min-height:480px;background:#0d1117;border:1px solid rgba(255,255,255,.1);border-radius:8px;padding:1.25rem;font-family:'JetBrains Mono',monospace;font-size:.88rem;color:#e8eaf0;outline:none;white-space:pre-wrap;overflow-y:auto;line-height:1.7;tab-size:4;"
                            oninput="syncContent()">{{ $template ?? '' }}</div>

                        <div class="mt-3 d-flex gap-2 align-items-center flex-wrap">
                            <button type="submit" class="btn btn-theme px-4">
                                <i class="bi bi-check2 me-1"></i>{{ $isEdit ? 'Save Changes' : 'Create Lesson' }}
                            </button>
                            <a href="{{ route('admin.content.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <span class="ms-auto fs-12px text-muted">Tip: Use front-matter panel to set content type, then
                                write markdown below.</span>
                        </div>
                    </form>
                </div>
                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>
            </div>

            <!-- Media URL Helper -->
            <div class="card mt-3">
                <div class="card-header py-2"><span class="fw-semibold fs-13px"><i
                            class="bi bi-images me-2 text-theme"></i>Quick Media Insert</span></div>
                <div class="card-body p-3">
                    <p class="fs-12px text-muted mb-2">Paste these into your markdown to embed media:</p>
                    <code
                        class="d-block fs-12px bg-black p-2 rounded mb-1">![Image description](/storage/media/your-image.jpg)</code>
                    <code
                        class="d-block fs-12px bg-black p-2 rounded mb-1">&lt;iframe src="https://www.youtube.com/embed/VIDEO_ID" ...&gt;&lt;/iframe&gt;</code>
                    <a href="{{ route('admin.media.index') }}" class="btn btn-outline-theme btn-sm mt-2">
                        <i class="bi bi-images me-1"></i>Open Media Library
                    </a>
                </div>
                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function syncContent() {
            document.getElementById('markdownEditor').value = document.getElementById('codeEditor').innerText;
        }

        // Prevent div from converting to formatted HTML
        document.getElementById('codeEditor').addEventListener('paste', function (e) {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text');
            document.execCommand('insertText', false, text);
        });

        document.getElementById('editorForm').addEventListener('submit', function () {
            document.getElementById('markdownEditor').value = document.getElementById('codeEditor').innerText;
            this.querySelector('textarea[name=content]').removeAttribute('class');
        });

        // Tab key inserts spaces instead of leaving field
        document.getElementById('codeEditor').addEventListener('keydown', function (e) {
            if (e.key === 'Tab') {
                e.preventDefault();
                document.execCommand('insertText', false, '    ');
            }
        });

        let selectedType = 'lesson';
        function selectType(type) {
            selectedType = type;
            document.querySelectorAll('.type-btn').forEach(b => b.style.opacity = b.dataset.type === type ? '1' : '.5');
            document.getElementById('videoField').style.display = type === 'video' ? 'block' : 'none';
            document.getElementById('thumbField').style.display = type === 'video' ? 'block' : 'none';
        }
        selectType('lesson');

        function rebuildFrontmatter() {
            const title = document.getElementById('fmTitle').value || 'Untitled';
            const video = document.getElementById('fmVideo').value;
            const thumb = document.getElementById('fmThumb').value;
            let fm = `---\ntype: ${selectedType}\ntitle: "${title}"`;
            if (video) fm += `\nvideo: "${video}"`;
            if (thumb) fm += `\nthumbnail: "${thumb}"`;
            fm += '\n---\n\n';
            const current = document.getElementById('codeEditor').innerText;
            // Replace old front-matter if present
            const stripped = current.replace(/^---[\s\S]*?---\n\n?/, '');
            document.getElementById('codeEditor').innerText = fm + stripped;
            syncContent();
        }
    </script>
@endpush