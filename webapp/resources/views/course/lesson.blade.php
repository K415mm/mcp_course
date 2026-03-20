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
                                <a href="{{ route('course.lesson', [$module['slug'], $l['section'], $l['slug']]) }}" class="d-block text-decoration-none text-inverse px-3 py-2 lesson-nav-item
                                          {{ ($l['slug'] === $lessonSlug && $sectionKey === $section) ? 'active' : '' }}">
                                    @if(($l['type'] ?? '') === 'diagram')
                                        <i class="bi bi-bezier2 me-2 opacity-40 fs-12px text-theme"></i>
                                    @else
                                        <i class="bi bi-file-earmark-text me-2 opacity-40 fs-12px"></i>
                                    @endif
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
                @case('diagram')
                    {{-- Dedicated full-height diagram viewer embedded in the lesson --}}
                    <div class="card" style="height: calc(100vh - 260px); overflow:hidden;">
                        @if(isset($diagram) && $diagram->hasContent())
                            <iframe id="drawio-lesson-view"
                                    src="https://embed.diagrams.net/?embed=1&proto=json&ui=dark&spin=1&nav=1"
                                    style="width:100%;height:100%;border:none;"></iframe>
                        @else
                            <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                <div class="text-center"><i class="bi bi-diagram-3" style="font-size:3rem;opacity:.3;"></i><p class="mt-3">No diagram content yet.</p></div>
                            </div>
                        @endif
                        <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                    </div>
                    @break
                @default
                    <div class="card mb-4" style="border-color:rgba(255,255,255,.07);">
                        <div class="card-body p-4 p-lg-5">
                            <div class="md-content">
                                {!! $contentHtml !!}
                            </div>
                        </div>
                        <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                    </div>
            @endswitch


            {{-- ── Prev / Next Navigation ──────────────────────────────────── --}}
            <div class="d-flex justify-content-between align-items-center mt-2">
                @if($prevLesson)
                    <a href="{{ route('course.lesson', [$module['slug'], $prevLesson['section'], $prevLesson['slug']]) }}"
                        class="btn btn-outline-theme">
                        <i class="bi bi-chevron-left me-1"></i> {{ $prevLesson['title'] }}
                    </a>
                @else
                    <div></div>
                @endif

                <button id="mark-lesson-complete-btn" class="btn btn-outline-success mx-3" disabled>
                    <i class="bi bi-check2-circle me-1"></i> Complete Lesson (<span id="lesson-time-status">Waiting...</span>)
                </button>

                @if($nextLesson)
                    <a href="{{ route('course.lesson', [$module['slug'], $nextLesson['section'], $nextLesson['slug']]) }}"
                        class="btn btn-theme">
                        {{ $nextLesson['title'] }} <i class="bi bi-chevron-right ms-1"></i>
                    </a>
                @else
                    <div></div>
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

        <div id="drawer-editor" style="flex: 1; overflow-y: auto; display: flex; flex-direction: column;">
            <div id="quill-drawer-editor" style="flex: 1;"></div>
        </div>

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
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<style>
    .btn-xs { padding: 2px 8px; font-size: 12px; }
    #notes-fab:hover { transform: scale(1.1); }
    #drawer-editor .ql-container { border: none !important; }
    #drawer-editor .ql-editor { color: rgba(255,255,255,.87); font-size: 14px; min-height: 150px; }
    #drawer-editor .ql-editor.ql-blank::before { color: rgba(255,255,255,.25); font-style: normal; }
    #drawer-editor .ql-toolbar { background: rgba(0,0,0,.3); border-color: rgba(255,255,255,.1) !important; }
    #drawer-editor .ql-toolbar button, #drawer-editor .ql-picker { color: rgba(255,255,255,.7); }
</style>
<script>
(function() {
    const fab = document.getElementById('notes-fab');
    const drawer = document.getElementById('notes-drawer');
    const closeBtn = document.getElementById('notes-drawer-close');
    const moduleSlug = '{{ $module["slug"] }}';
    const csrf = '{{ csrf_token() }}';
    let quill = null;
    let drawerNoteId = null;
    let saveTimer = null;

    // Toggle drawer open/close
    fab.addEventListener('click', () => {
        const isOpen = drawer.dataset.open === '1';
        if (!isOpen) {
            drawer.style.transform = 'translateY(0px)';
            drawer.dataset.open = '1';
            if (!quill) initDrawer();
        } else {
            drawer.style.transform = 'translateY(110%)';
            drawer.dataset.open = '0';
        }
    });
    closeBtn.addEventListener('click', () => {
        drawer.style.transform = 'translateY(110%)';
        drawer.dataset.open = '0';
    });

    async function initDrawer() {
        // Init Quill on the inner wrapper div
        quill = new Quill('#quill-drawer-editor', {
            theme: 'snow',
            placeholder: 'Jot down notes for this lesson…',
            modules: { toolbar: [
                ['bold','italic','underline'],
                [{ header: [1,2,3,false] }],
                [{ list:'bullet' }, { list:'ordered' }],
                ['code-block', 'blockquote'],
                ['clean']
            ]}
        });

        // Load or create note for this module
        const res = await fetch(`/notes/module/${moduleSlug}`);
        const notes = await res.json();
        if (notes.length) {
            drawerNoteId = notes[0].id;
        } else {
            const cr = await fetch('/notes/create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ title: 'Notes: {{ $module["title"] }}', module_slug: moduleSlug })
            });
            if (cr.ok) { const d = await cr.json(); drawerNoteId = d.id; }
        }

        quill.on('text-change', () => {
            clearTimeout(saveTimer);
            document.getElementById('drawer-save-status').textContent = 'Saving…';
            saveTimer = setTimeout(saveDrawer, 1500);
        });
    }

    async function saveDrawer() {
        if (!drawerNoteId || !quill) return;
        const body = quill.root.innerHTML;
        const res = await fetch(`/notes/${drawerNoteId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ body })
        });
        if (res.ok) document.getElementById('drawer-save-status').textContent = 'Saved';
    }

    document.getElementById('drawer-md-export').addEventListener('click', () => {
        if (!quill) return;
        const md = quill.root.innerHTML.replace(/<[^>]+>/g, '');
        const b = new Blob([md], {type:'text/markdown'});
        const a = document.createElement('a'); a.href = URL.createObjectURL(b);
        a.download = 'lesson-notes.md'; a.click();
    });
})();
</script>

@if($contentType === 'diagram' && isset($diagram) && $diagram->hasContent())
<script>
(function() {
    const frame = document.getElementById('drawio-lesson-view');
    if (!frame) return;
    const fileUrl = "{{ route('diagrams.file', $diagram->id) }}";
    window.addEventListener('message', function(evt) {
        if (evt.source !== frame.contentWindow) return;
        let msg; try { msg = JSON.parse(evt.data); } catch(e) { return; }
        if (msg.event === 'init') {
            fetch(fileUrl, { headers: { 'Accept': 'application/xml' } })
                .then(r => r.text())
                .then(xml => frame.contentWindow.postMessage(JSON.stringify({ action: 'load', xml, autosave: 0 }), '*'));
        }
    });
})();
</script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const minSeconds = {{ config('course.min_lesson_time', 60) }};
    const pingInterval = 10; // Ping every 10 seconds
    const courseSlug = '{{ $module["course_slug"] ?? "legacy" }}';
    const moduleSlug = '{{ $module["slug"] }}';
    const lessonSlug = '{{ $lessonSlug }}';
    const csrfToken = '{{ csrf_token() }}';
    
    const btn = document.getElementById('mark-lesson-complete-btn');
    const statusTxt = document.getElementById('lesson-time-status');
    let timeSpent = 0;
    let isCompleted = false;

    // Send ping every X seconds
    const pingTimer = setInterval(async () => {
        if (isCompleted) {
            clearInterval(pingTimer);
            return;
        }

        try {
            const res = await fetch('{{ route("progress.ping") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    course_slug: courseSlug,
                    module_slug: moduleSlug,
                    lesson_slug: lessonSlug,
                    increment: pingInterval
                })
            });

            if (res.ok) {
                const data = await res.json();
                timeSpent = data.time_spent;
                isCompleted = data.is_completed;
                updateButtonState();
            }
        } catch(e) { console.error('Ping failed', e); }
    }, pingInterval * 1000);

    function updateButtonState() {
        if (isCompleted) {
            btn.classList.remove('btn-outline-success');
            btn.classList.add('btn-success');
            btn.disabled = true;
            statusTxt.textContent = 'Completed!';
            return;
        }

        const remaining = minSeconds - timeSpent;
        if (remaining > 0) {
            btn.disabled = true;
            statusTxt.textContent = remaining + 's remaining';
        } else {
            btn.disabled = false;
            statusTxt.textContent = 'Ready!';
        }
    }

    // Do an initial ping to get current progress immediately
    setTimeout(() => {
        fetch('{{ route("progress.ping") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ course_slug: courseSlug, module_slug: moduleSlug, lesson_slug: lessonSlug, increment: 1 }) // Small initial increment
        }).then(r => r.json()).then(data => {
            timeSpent = data.time_spent;
            isCompleted = data.is_completed;
            updateButtonState();
        }).catch(e => console.error(e));
    }, 1000);

    // Handle Complete Click
    btn.addEventListener('click', async () => {
        btn.disabled = true;
        statusTxt.textContent = 'Verifying...';
        
        try {
            const res = await fetch('{{ route("progress.complete") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ course_slug: courseSlug, module_slug: moduleSlug, lesson_slug: lessonSlug })
            });
            const data = await res.json();
            
            if (res.ok && data.status === 'completed') {
                isCompleted = true;
                updateButtonState();
                // Check if there is a next lesson link, visually highlight it
            } else {
                alert(data.message || 'Verification failed. Did you spend enough time?');
                updateButtonState(); // Re-evaluate
            }
        } catch(e) {
            console.error('Complete failed', e);
            btn.disabled = false;
        }
    });
});
</script>
@endpush