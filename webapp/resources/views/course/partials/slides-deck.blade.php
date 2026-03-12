{{-- SLIDES DECK PARTIAL --}}
{{-- Splits content on
<hr> tags (--- in markdown) into individual slides --}}
{{-- Expects: $contentHtml, $meta --}}

@php
    // Split on <hr> tags to create slides
    $slides = preg_split('/<hr\s*\/?>/', $contentHtml);
    $slides = array_values(array_filter(array_map('trim', $slides)));
    if (empty($slides))
        $slides = [$contentHtml];
    $total = count($slides);
@endphp

<div class="mb-3 d-flex align-items-center gap-2">
    <span class="badge bg-primary fs-12px px-3 py-2"><i class="bi bi-collection-play me-1"></i>Slides</span>
    <h5 class="fw-bold text-inverse mb-0">{{ $meta['title'] ?? ($lessonTitle ?? 'Slides') }}</h5>
    <span class="ms-auto text-muted fs-12px"><span id="slideNum">1</span> / {{ $total }}</span>
</div>

{{-- Slide Deck --}}
<div class="card" style="min-height:480px;">
    <div class="card-body p-5 d-flex flex-column justify-content-center" id="slideContent">
        <div class="md-content slide-body"></div>
    </div>
    <div class="card-arrow">
        <div class="card-arrow-top-left"></div>
        <div class="card-arrow-top-right"></div>
        <div class="card-arrow-bottom-left"></div>
        <div class="card-arrow-bottom-right"></div>
    </div>
</div>

{{-- Progress dots --}}
<div class="d-flex justify-content-center gap-2 mt-3 mb-3" id="slideDots">
    @for($i = 0; $i < $total; $i++)
        <button class="slide-dot" data-slide="{{ $i }}" onclick="goSlide({{ $i }})"
            style="width:10px;height:10px;border-radius:50%;border:none;background:rgba(255,255,255,.2);cursor:pointer;transition:.2s;padding:0;"></button>
    @endfor
</div>

{{-- Navigation --}}
<div class="d-flex justify-content-between align-items-center">
    <button id="prevBtn" onclick="prevSlide()" class="btn btn-outline-secondary" disabled>
        <i class="bi bi-arrow-left me-1"></i>Previous
    </button>
    <div class="d-flex gap-2">
        <button onclick="toggleFullscreen()" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-fullscreen"></i>
        </button>
        <button id="nextBtn" onclick="nextSlide()" class="btn btn-outline-theme">
            Next <i class="bi bi-arrow-right ms-1"></i>
        </button>
    </div>
</div>

@push('scripts')
    <script>
        const slides = @json($slides);
        let current = 0;

        function renderSlide(i) {
            const content = document.querySelector('.slide-body');
            content.style.opacity = '0';
            content.style.transform = 'translateY(10px)';
            setTimeout(() => {
                content.innerHTML = slides[i];
                content.style.opacity = '1';
                content.style.transform = 'translateY(0)';
                // Re-highlight code
                if (window.hljs) content.querySelectorAll('pre code').forEach(b => hljs.highlightElement(b));
            }, 150);
            document.getElementById('slideNum').textContent = i + 1;
            document.querySelectorAll('.slide-dot').forEach((d, di) => {
                d.style.background = di === i ? 'var(--bs-theme)' : 'rgba(255,255,255,.2)';
                d.style.transform = di === i ? 'scale(1.3)' : 'scale(1)';
            });
            document.getElementById('prevBtn').disabled = i === 0;
            document.getElementById('nextBtn').disabled = i === slides.length - 1;
            document.getElementById('nextBtn').textContent = i === slides.length - 1 ? 'Finish ✓' : 'Next →';
        }

        function nextSlide() { if (current < slides.length - 1) { current++; renderSlide(current); } }
        function prevSlide() { if (current > 0) { current--; renderSlide(current); } }
        function goSlide(i) { current = i; renderSlide(i); }

        function toggleFullscreen() {
            const el = document.querySelector('.card');
            if (!document.fullscreenElement) el.requestFullscreen();
            else document.exitFullscreen();
        }

        // Keyboard navigation
        document.addEventListener('keydown', e => {
            if (e.key === 'ArrowRight' || e.key === 'ArrowDown') nextSlide();
            if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') prevSlide();
        });

        // Init
        document.querySelector('.slide-body').style.transition = 'opacity .15s, transform .15s';
        renderSlide(0);
    </script>
@endpush