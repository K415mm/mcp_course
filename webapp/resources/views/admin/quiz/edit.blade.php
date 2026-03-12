@extends('layouts.app')

@section('title', 'Quiz Builder — Admin')

@section('content')
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-theme">Admin</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.content.index') }}" class="text-theme">Content</a></li>
        <li class="breadcrumb-item active">Quiz Builder</li>
    </ol>
</nav>

@if(session('success'))
<div class="alert alert-success alert-dismissible fs-13px mb-4"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="fw-bold mb-0 text-inverse">
                <i class="bi bi-question-circle text-theme me-2"></i>Quiz Builder
                <small class="fs-13px text-muted fw-normal ms-1">{{ $moduleSlug }} / {{ $lessonSlug }}</small>
            </h4>
            <button onclick="addQuestion()" class="btn btn-outline-theme btn-sm">
                <i class="bi bi-plus-circle me-1"></i>Add Question
            </button>
        </div>

        <form method="POST" action="{{ route('admin.quiz.update', [$moduleSlug, $lessonSlug]) }}" id="quizForm">
            @csrf @method('PUT')

            <div id="questionsList">
                @php $questions = $quiz?->questions ?? []; @endphp
                @foreach($questions as $qi => $q)
                <div class="card mb-3 question-card" id="q-{{ $qi }}">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-theme text-dark fs-11px">Q{{ $qi + 1 }}</span>
                            <span class="text-muted fs-12px flex-fill">Question {{ $qi + 1 }}</span>
                            <button type="button" class="btn btn-xs btn-outline-danger" onclick="this.closest('.question-card').remove();renumber()">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fs-12px fw-semibold">Question</label>
                            <input type="text" name="questions[{{ $qi }}][q]" value="{{ $q['q'] }}"
                                   class="form-control bg-inverse bg-opacity-5" placeholder="What is...?" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fs-12px fw-semibold">Options (A, B, C, D)</label>
                            <div class="row g-2">
                                @foreach(['A','B','C','D'] as $letter)
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text bg-inverse bg-opacity-10 text-theme fw-bold fs-12px">{{ $letter }}</span>
                                        <input type="text" name="questions[{{ $qi }}][options][{{ $loop->index }}]"
                                               value="{{ $q['options'][$loop->index] ?? '' }}"
                                               class="form-control bg-inverse bg-opacity-5" placeholder="Option {{ $letter }}">
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fs-12px fw-semibold">Correct Answer</label>
                                <select name="questions[{{ $qi }}][answer]" class="form-select bg-inverse bg-opacity-5">
                                    @foreach(['A','B','C','D'] as $letter)
                                    <option value="{{ $letter }}" {{ ($q['answer']??'A')===$letter?'selected':'' }}>{{ $letter }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-9">
                                <label class="form-label fs-12px fw-semibold">Explanation</label>
                                <input type="text" name="questions[{{ $qi }}][explanation]" value="{{ $q['explanation']??'' }}"
                                       class="form-control bg-inverse bg-opacity-5" placeholder="Why is this the correct answer?">
                            </div>
                        </div>
                    </div>
                    <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                </div>
                @endforeach
            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-theme px-4"><i class="bi bi-check2 me-1"></i>Save Quiz</button>
                <a href="{{ route('admin.content.index') }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let qCount = {{ count($quiz?->questions ?? []) }};
function addQuestion() {
    const qi = qCount++;
    const html = `
    <div class="card mb-3 question-card" id="q-${qi}">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="badge bg-theme text-dark fs-11px qnum">Q${qi+1}</span>
                <span class="text-muted fs-12px flex-fill">Question ${qi+1}</span>
                <button type="button" class="btn btn-xs btn-outline-danger" onclick="this.closest('.question-card').remove();renumber()"><i class="bi bi-trash"></i></button>
            </div>
            <div class="mb-3">
                <label class="form-label fs-12px fw-semibold">Question</label>
                <input type="text" name="questions[${qi}][q]" class="form-control bg-inverse bg-opacity-5" placeholder="What is...?" required>
            </div>
            <div class="mb-3">
                <label class="form-label fs-12px fw-semibold">Options (A, B, C, D)</label>
                <div class="row g-2">
                    ${['A','B','C','D'].map((l,i)=>`<div class="col-md-6"><div class="input-group"><span class="input-group-text bg-inverse bg-opacity-10 text-theme fw-bold fs-12px">${l}</span><input type="text" name="questions[${qi}][options][${i}]" class="form-control bg-inverse bg-opacity-5" placeholder="Option ${l}"></div></div>`).join('')}
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fs-12px fw-semibold">Correct Answer</label>
                    <select name="questions[${qi}][answer]" class="form-select bg-inverse bg-opacity-5">
                        ${['A','B','C','D'].map(l=>`<option value="${l}">${l}</option>`).join('')}
                    </select>
                </div>
                <div class="col-md-9">
                    <label class="form-label fs-12px fw-semibold">Explanation</label>
                    <input type="text" name="questions[${qi}][explanation]" class="form-control bg-inverse bg-opacity-5" placeholder="Why is this the correct answer?">
                </div>
            </div>
        </div>
        <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
    </div>`;
    document.getElementById('questionsList').insertAdjacentHTML('beforeend', html);
}
function renumber() {
    document.querySelectorAll('.question-card').forEach((card,i)=>{
        card.querySelectorAll('.qnum').forEach(b=>b.textContent=`Q${i+1}`);
    });
}
</script>
<style>.btn-xs{padding:.2rem .5rem;font-size:.75rem;}</style>
@endpush
