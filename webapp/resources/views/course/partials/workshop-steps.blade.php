{{-- WORKSHOP STEPS PARTIAL --}}
{{-- Renders .md with ## headings as accordion step panels --}}
{{-- Expects: $contentHtml, $meta --}}

@php
    // Parse H2 headings from rendered HTML to build accordion steps
    preg_match_all('/<h2[^>]*>(.*?)<\/h2>([\s\S]*?)(?=<h2|$)/', $contentHtml, $matches, PREG_SET_ORDER);
    $steps = [];
    foreach ($matches as $i => $m) {
        $steps[] = ['title' => strip_tags($m[1]), 'body' => trim($m[2])];
    }
    $hasSteps = !empty($steps);
@endphp

<div class="mb-4 d-flex align-items-center gap-2">
    <span class="badge bg-success fs-12px px-3 py-2"><i class="bi bi-tools me-1"></i>Workshop</span>
    <h5 class="fw-bold text-inverse mb-0">{{ $meta['title'] ?? ($lessonTitle ?? 'Workshop') }}</h5>
</div>

@if(!$hasSteps)
    {{-- No H2 headings — render as plain markdown --}}
    <div class="md-content">{!! $contentHtml !!}</div>
@else
    <p class="text-muted fs-13px mb-3">
        <i class="bi bi-info-circle me-1"></i>
        {{ count($steps) }} step{{ count($steps) !== 1 ? 's' : '' }} — click each step to expand.
    </p>

    <div class="accordion" id="workshopAccordion">
        @foreach($steps as $si => $step)
            <div class="accordion-item mb-2"
                style="background:#1a1d23;border:1px solid rgba(255,255,255,.08);border-radius:8px;overflow:hidden;">
                <h2 class="accordion-header">
                    <button class="accordion-button {{ $si > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse"
                        data-bs-target="#step-{{ $si }}"
                        style="background:#23262d;color:#e8eaf0;font-weight:600;font-size:.95rem;">
                        <span class="badge bg-theme text-dark me-2 flex-shrink-0" style="min-width:28px;">{{ $si + 1 }}</span>
                        {{ $step['title'] }}
                    </button>
                </h2>
                <div id="step-{{ $si }}" class="accordion-collapse collapse {{ $si === 0 ? 'show' : '' }}"
                    data-bs-parent="#workshopAccordion">
                    <div class="accordion-body md-content p-4">
                        {!! $step['body'] !!}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Progress tracker --}}
    <div class="card mt-4" style="border-color:rgba(var(--bs-theme-rgb),.2);">
        <div class="card-body p-3 d-flex align-items-center gap-3">
            <i class="bi bi-check2-all text-theme" style="font-size:1.4rem;"></i>
            <div class="flex-fill">
                <div class="d-flex justify-content-between mb-1">
                    <span class="fs-13px fw-semibold text-inverse">Workshop Progress</span>
                    <span id="progressPct" class="fs-13px text-theme fw-bold">0%</span>
                </div>
                <div class="progress" style="height:6px;">
                    <div id="progressBar" class="progress-bar bg-theme" style="width:0%;"></div>
                </div>
            </div>
            <div class="fs-12px text-muted"><span id="stepsOpened">0</span>/{{ count($steps) }} steps</div>
        </div>
        <div class="card-arrow">
            <div class="card-arrow-top-left"></div>
            <div class="card-arrow-top-right"></div>
            <div class="card-arrow-bottom-left"></div>
            <div class="card-arrow-bottom-right"></div>
        </div>
    </div>

    @push('scripts')
        <script>
            let openedSteps = new Set([0]); // first step is open by default
            const total = {{ count($steps) }};
            function updateProgress() {
                const pct = Math.round(openedSteps.size / total * 100);
                document.getElementById('progressBar').style.width = pct + '%';
                document.getElementById('progressPct').textContent = pct + '%';
                document.getElementById('stepsOpened').textContent = openedSteps.size;
            }
            document.querySelectorAll('.accordion-button').forEach((btn, i) => {
                btn.addEventListener('click', () => {
                    if (!btn.classList.contains('collapsed')) openedSteps.add(i);
                    setTimeout(updateProgress, 50);
                });
            });
            updateProgress();
        </script>
    @endpush
@endif