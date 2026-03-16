@extends('layouts.app')

@section('title', $lessonTitle . ' — ' . config('course.title'))

@section('content')
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-theme">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('course.index') }}" class="text-theme">All Modules</a></li>
            <li class="breadcrumb-item"><a href="{{ route('course.module', $module['slug']) }}"
                    class="text-theme">{{ $module['title'] }}</a></li>
            <li class="breadcrumb-item active">{{ $lessonTitle }}</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Lesson List Sidebar -->
        <div class="col-xl-3 col-lg-4 mb-4">
            <div class="d-flex align-items-center mb-3 gap-2">
                <i class="bi {{ $module['icon'] }} text-theme"></i>
                <span class="fw-semibold fs-13px text-inverse">{{ $module['title'] }}</span>
            </div>

            @foreach($lessons as $sectionKey => $sectionData)
                <div class="card mb-3">
                    <div class="card-body p-0">
                        <div class="px-3 py-2 border-bottom border-secondary">
                            <span class="fw-semibold fs-11px text-uppercase text-muted" style="letter-spacing:.07em;">
                                <i class="bi bi-folder2 me-1"></i>{{ $sectionData['label'] }}
                            </span>
                        </div>
                        <div class="py-1">
                            @foreach($sectionData['lessons'] as $l)
                                <a href="{{ route('course.lesson', [$module['slug'], $sectionKey, $l['slug']]) }}" class="d-block text-decoration-none text-inverse px-3 py-2 lesson-nav-item
                                          {{ ($l['slug'] === $lessonSlug && $sectionKey === $section) ? 'active' : '' }}">
                                    <i class="bi bi-file-earmark-text me-2 opacity-40 fs-12px"></i>
                                    <span class="fs-12px">{{ $l['title'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                    <div class="card-arrow">
                        <div class="card-arrow-top-left"></div>
                        <div class="card-arrow-top-right"></div>
                        <div class="card-arrow-bottom-left"></div>
                        <div class="card-arrow-bottom-right"></div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Main Lesson Content -->
        <div class="col-xl-9 col-lg-8">
            <!-- Section badge -->
            <div class="mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                @php $sections = config('course.sections'); @endphp
                <span class="badge bg-dark border border-secondary text-muted px-3 py-2 fs-11px">
                    <i class="bi bi-folder2-open me-1"></i>
                    {{ $sections[$section] ?? ucfirst($section) }}
                </span>
                <a href="{{ route('course.module', $module['slug']) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Module
                </a>
            </div>

            <!-- Dynamic Content Rendering -->
            @switch($contentType)
                @case('quiz')
                    @include('course.partials.quiz-renderer')
                    @break
                @case('video')
                    @include('course.partials.video-card')
                    @break
                @case('workshop')
                    @include('course.partials.workshop-steps')
                    @break
                @case('slides')
                    @include('course.partials.slides-deck')
                    @break
                @default
                    <!-- Lesson Content Card (Default Markdown) -->
                    <div class="card mb-4" style="border-color:rgba(255,255,255,.07);">
                        <div class="card-body p-4 p-lg-5">
                            <div class="md-content">
                                {!! $contentHtml !!}
                            </div>
                        </div>
                        <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                    </div>
            @endswitch

            <!-- Prev / Next Navigation -->
            <div class="d-flex justify-content-between align-items-center mt-2">
                @if($prevLesson)
                    <a href="{{ route('course.lesson', [$module['slug'], $prevLesson['section'], $prevLesson['slug']]) }}"
                        class="btn btn-outline-theme">
                        <i class="bi bi-chevron-left me-1"></i> {{ $prevLesson['title'] }}
                    </a>
                @else
                    <div></div>
                @endif

                @if($nextLesson)
                    <a href="{{ route('course.lesson', [$module['slug'], $nextLesson['section'], $nextLesson['slug']]) }}"
                        class="btn btn-theme">
                        {{ $nextLesson['title'] }} <i class="bi bi-chevron-right ms-1"></i>
                    </a>
                @endif
            </div>
        </div>
    @if($cinematicAnimationsEnabled ?? true)
        <!-- Cinematic Malware Glitch Effect -->
        <img id="cinematic-malware" src="{{ asset('img/workshops/malware_analysis.png') }}"
             alt="Malware Analysis"
             style="position: fixed; top: 20%; right: -5%; width: 500px; z-index: 0; pointer-events: none; opacity: 0; filter: drop-shadow(0 0 30px rgba(220,38,38,0.4)) grayscale(80%) contrast(150%); mix-blend-mode: screen;" />
    @endif

    {{-- ── Floating Notes Drawer ───────────────────────────────────── --}}
    <!-- FAB Toggle Button -->
    <button id="notes-fab" title="My Notes"
            style="position: fixed; bottom: 2rem; right: 2rem; z-index: 5000;
                   width: 56px; height: 56px; border-radius: 50%; border: none;
                   background: var(--bs-theme); color: #000; font-size: 1.4rem;
                   box-shadow: 0 4px 20px rgba(0,0,0,.5); cursor: pointer;
                   display: flex; align-items: center; justify-content: center;
                   transition: transform .2s;">
        <i class="bi bi-journal-text"></i>
    </button>

    <!-- Slide-in Note Drawer -->
    <div id="notes-drawer"
         style="position: fixed; bottom: 0; right: 0; width: 420px; max-width: 100vw; height: 60vh;
                z-index: 4999; background: #1a1f2e; border-top: 1px solid rgba(255,255,255,.08);
                border-left: 1px solid rgba(255,255,255,.08); border-radius: 12px 0 0 0;
                box-shadow: -4px -4px 30px rgba(0,0,0,.5); transform: translateY(110%);
                transition: transform .3s cubic-bezier(.4,0,.2,1); display: flex; flex-direction: column;">

        <div class="d-flex align-items-center justify-content-between p-3 border-bottom border-secondary">
            <span class="fw-bold fs-13px"><i class="bi bi-journal-text me-2 text-theme"></i>Quick Notes</span>
            <div class="d-flex gap-2">
                <a href="{{ route('notes.index') }}" class="btn btn-xs btn-outline-theme" target="_blank">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Open Full Notes
                </a>
                <button id="drawer-md-export" class="btn btn-xs btn-outline-success" title="Export .md">
                    <i class="bi bi-markdown"></i>
                </button>
                <button id="notes-drawer-close" class="btn btn-xs btn-outline-secondary"><i class="bi bi-x-lg"></i></button>
            </div>
        </div>

        <div id="drawer-editor" style="flex: 1; overflow-y: auto; padding: 1rem; outline: none; font-size: 14px; line-height: 1.7;"></div>

        <div class="px-3 py-2 border-top border-secondary d-flex justify-content-between align-items-center">
            <span id="drawer-save-status" class="text-muted fs-11px">Auto-saved</span>
            <a href="{{ route('notes.index') }}" class="fs-11px text-theme">View all my notes →</a>
        </div>
    </div>

@endsection

@if($cinematicAnimationsEnabled ?? true)
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof gsap !== 'undefined') {
                const malwareImg = document.getElementById('cinematic-malware');
                if (malwareImg) {
                    // Fade in slowly to a base opacity
                    gsap.to(malwareImg, { opacity: 0.08, duration: 2, delay: 1 });
                    
                    // Glitch effect loop: random snappy jumps in position, opacity, and skew
                    gsap.to(malwareImg, {
                        x: "random(-15, 15)",
                        y: "random(-15, 15)",
                        skewX: "random(-20, 20)",
                        opacity: "random(0.03, 0.2)",
                        duration: 0.15,
                        repeat: -1,
                        repeatRefresh: true,
                        ease: "steps(1)"
                    });
                    
                    // Very slow ambient rotation
                    gsap.to(malwareImg, {
                        rotation: 3,
                        duration: 4,
                        repeat: -1,
                        yoyo: true,
                        ease: "sine.inOut"
                    });
                }
            }
        });
    </script>
    @endpush
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@tiptap/core@2.2.4/dist/index.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/starter-kit@2.2.4/dist/index.umd.js"></script>
<style>
    .btn-xs { padding: 2px 8px; font-size: 12px; }
    #drawer-editor .ProseMirror { outline: none; min-height: 200px; color: rgba(255,255,255,.85); }
    #drawer-editor p.is-editor-empty:first-child::before { content: attr(data-placeholder); float: left; color: rgba(255,255,255,.2); pointer-events: none; height: 0; }
    #notes-fab:hover { transform: scale(1.1); }
</style>
<script>
(function() {
    const { Editor } = window['@tiptap/core'];
    const StarterKit = window['@tiptap/starter-kit'].StarterKit;

    const fab = document.getElementById('notes-fab');
    const drawer = document.getElementById('notes-drawer');
    const closeBtn = document.getElementById('notes-drawer-close');
    const moduleSlug = '{{ $module["slug"] }}';
    const csrf = '{{ csrf_token() }}';
    let drawerEditor = null;
    let drawerNoteId = null;
    let saveTimer = null;

    // Toggle drawer
    fab.addEventListener('click', () => {
        const isOpen = drawer.style.transform === 'translateY(0px)';
        drawer.style.transform = isOpen ? 'translateY(110%)' : 'translateY(0px)';
        if (!isOpen && !drawerEditor) initDrawerEditor();
    });
    closeBtn.addEventListener('click', () => { drawer.style.transform = 'translateY(110%)'; });

    async function initDrawerEditor() {
        // Try to load an existing note for this module, else create one
        const res = await fetch(`/notes/module/${moduleSlug}`);
        const notes = await res.json();
        if (notes.length) {
            drawerNoteId = notes[0].id;
            const noteRes = await fetch(`/notes/view/${drawerNoteId}`);
            // Redirect is not needed — fetch the JSON data instead
        } else {
            // Create a new note scoped to this module
            const createRes = await fetch('/notes/create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ title: 'Notes for {{ $module["title"] }}', module_slug: moduleSlug })
            });
            if (createRes.ok) {
                const data = await createRes.json();
                drawerNoteId = data.id;
            }
        }

        drawerEditor = new Editor({
            element: document.getElementById('drawer-editor'),
            extensions: [ StarterKit ],
            content: '',
            onUpdate: () => {
                clearTimeout(saveTimer);
                document.getElementById('drawer-save-status').textContent = 'Saving...';
                saveTimer = setTimeout(saveDrawer, 1500);
            }
        });
    }

    async function saveDrawer() {
        if (!drawerNoteId || !drawerEditor) return;
        const body = JSON.stringify(drawerEditor.getJSON());
        const res = await fetch(`/notes/${drawerNoteId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ body })
        });
        if (res.ok) document.getElementById('drawer-save-status').textContent = 'Saved';
    }

    // Markdown export from drawer
    document.getElementById('drawer-md-export').addEventListener('click', () => {
        if (!drawerEditor) return;
        const html = document.getElementById('drawer-editor').innerHTML;
        const md = html.replace(/<h1[^>]*>(.*?)<\/h1>/gi, '# $1\n').replace(/<p[^>]*>(.*?)<\/p>/gi, '$1\n\n').replace(/<[^>]+>/g, '');
        const blob = new Blob([md], { type: 'text/markdown' });
        const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = 'lesson-notes.md'; a.click();
    });
})();
</script>
@endpush