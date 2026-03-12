{{-- QUIZ RENDERER PARTIAL --}}
{{-- Expects: $quiz (Quiz model or null), $module, $lessonSlug, $contentHtml (for instructions fallback) --}}

@if(!$quiz || empty($quiz->questions))
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        This lesson is marked as a <strong>quiz</strong> but no questions have been configured yet.
        @if(Auth::user()->isAdmin())
            <a href="{{ route('admin.quiz.edit', [$module['slug'], $lessonSlug]) }}" class="alert-link ms-2">
                <i class="bi bi-pencil"></i> Build Quiz
            </a>
        @endif
    </div>
    @if($contentHtml)
        <div class="md-content">{!! $contentHtml !!}</div>
    @endif

@else
    {{-- Instructions from markdown body (optional) --}}
    @if($contentHtml && trim(strip_tags($contentHtml)))
        <div class="md-content mb-4">{!! $contentHtml !!}</div>
        <hr class="border-secondary mb-4">
    @endif

    <div id="quizContainer">
        <form id="quizForm" onsubmit="gradeQuiz(event)">
            @csrf
            @foreach($quiz->questions as $qi => $q)
                <div class="card mb-3 quiz-question" id="qq-{{ $qi }}">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <span class="badge bg-theme text-dark flex-shrink-0 mt-1"
                                style="min-width:28px;font-size:.8rem;">{{ $qi + 1 }}</span>
                            <p class="fw-semibold text-inverse mb-0 fs-15px">{{ $q['q'] }}</p>
                        </div>
                        <div class="options-grid ps-4">
                            @foreach($q['options'] as $oi => $option)
                                @php $letter = ['A', 'B', 'C', 'D'][$oi] ?? chr(65 + $oi); @endphp
                                <label class="option-label d-flex align-items-center gap-3 p-3 rounded-3 mb-2 cursor-pointer"
                                    style="border:1px solid rgba(255,255,255,.1);cursor:pointer;transition:.15s;"
                                    data-qi="{{ $qi }}" data-letter="{{ $letter }}">
                                    <input type="radio" name="q{{ $qi }}" value="{{ $letter }}" class="d-none"
                                        onchange="highlightOption(this)">
                                    <span class="option-badge"
                                        style="width:28px;height:28px;border-radius:50%;border:2px solid rgba(var(--bs-theme-rgb),.4);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.75rem;color:var(--bs-theme);flex-shrink:0;">{{ $letter }}</span>
                                    <span>{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                        {{-- Feedback (shown after submit) --}}
                        <div class="quiz-feedback ps-4 mt-2" style="display:none;"></div>
                    </div>
                    <div class="card-arrow">
                        <div class="card-arrow-top-left"></div>
                        <div class="card-arrow-top-right"></div>
                        <div class="card-arrow-bottom-left"></div>
                        <div class="card-arrow-bottom-right"></div>
                    </div>
                </div>
            @endforeach

            <button type="submit" id="submitBtn" class="btn btn-theme btn-lg px-5">
                <i class="bi bi-check2-circle me-2"></i>Submit Answers
            </button>
        </form>

        {{-- Score Panel (hidden until submit) --}}
        <div id="scorePanel" class="card mt-4" style="display:none;border-color:rgba(var(--bs-theme-rgb),.3);">
            <div class="card-body p-4 text-center">
                <div id="scoreCircle"
                    style="width:100px;height:100px;border-radius:50%;border:4px solid var(--bs-theme);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.8rem;font-weight:700;color:var(--bs-theme);">
                </div>
                <h5 id="scoreMsg" class="fw-bold text-inverse mb-1"></h5>
                <p id="scoreDetail" class="text-muted mb-3"></p>
                <button onclick="resetQuiz()" class="btn btn-outline-theme">
                    <i class="bi bi-arrow-repeat me-1"></i>Try Again
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

    @push('scripts')
        <script>
            const quizData = @json($quiz->questions);

            function highlightOption(input) {
                const qi = input.name.replace('q', '');
                document.querySelectorAll(`[name="${input.name}"]`).forEach(r => {
                    r.closest('label').style.borderColor = 'rgba(255,255,255,.1)';
                    r.closest('label').style.background = '';
                });
                input.closest('label').style.borderColor = 'var(--bs-theme)';
                input.closest('label').style.background = 'rgba(var(--bs-theme-rgb),.08)';
            }

            function gradeQuiz(e) {
                e.preventDefault();
                const answers = {};
                quizData.forEach((q, i) => {
                    const sel = document.querySelector(`input[name="q${i}"]:checked`);
                    answers[i] = sel ? sel.value : null;
                });

                let correct = 0;
                quizData.forEach((q, i) => {
                    const isOk = answers[i] === q.answer;
                    if (isOk) correct++;
                    const fb = document.querySelector(`#qq-${i} .quiz-feedback`);
                    fb.style.display = 'block';
                    fb.innerHTML = isOk
                        ? `<div class="alert alert-success py-2 fs-13px"><i class="bi bi-check-circle me-2"></i><strong>Correct!</strong> ${q.explanation || ''}</div>`
                        : `<div class="alert alert-danger py-2 fs-13px"><i class="bi bi-x-circle me-2"></i><strong>Incorrect.</strong> Correct answer: <strong>${q.answer}</strong>. ${q.explanation || ''}</div>`;
                    // Style selected option
                    const sel = document.querySelector(`input[name="q${i}"]:checked`);
                    if (sel) sel.closest('label').style.borderColor = isOk ? '#10b981' : '#dc3545';
                });

                const pct = Math.round(correct / quizData.length * 100);
                document.getElementById('scorePanel').style.display = 'block';
                document.getElementById('scoreCircle').textContent = pct + '%';
                document.getElementById('scoreMsg').textContent = pct >= 80 ? '🎉 Excellent work!' : pct >= 60 ? '👍 Good effort!' : '📚 Keep studying!';
                document.getElementById('scoreDetail').textContent = `${correct} out of ${quizData.length} correct`;
                document.getElementById('submitBtn').disabled = true;
                document.getElementById('scorePanel').scrollIntoView({ behavior: 'smooth' });
            }

            function resetQuiz() {
                document.getElementById('quizForm').reset();
                document.querySelectorAll('.quiz-feedback').forEach(f => f.style.display = 'none');
                document.querySelectorAll('.option-label').forEach(l => { l.style.borderColor = 'rgba(255,255,255,.1)'; l.style.background = ''; });
                document.getElementById('scorePanel').style.display = 'none';
                document.getElementById('submitBtn').disabled = false;
            }
        </script>
        <style>
            .option-label:hover {
                border-color: rgba(var(--bs-theme-rgb), .5) !important;
                background: rgba(var(--bs-theme-rgb), .04) !important;
            }
        </style>
    @endpush
@endif