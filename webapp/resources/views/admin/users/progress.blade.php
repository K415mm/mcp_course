@extends('layouts.app')
@section('title', 'User Progress: ' . $user->name . ' — Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-inverse">
            <i class="bi bi-person-lines-fill text-theme me-2"></i>{{ $user->name }}'s Progress
        </h4>
        <nav aria-label="breadcrumb" class="mb-0 mt-2">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-theme">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}" class="text-theme">Students & Users</a></li>
                <li class="breadcrumb-item active">{{ $user->name }}</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Stat 1 -->
    <div class="col-md-4">
        <div class="card h-100 bg-dark border-theme" style="box-shadow: 0 0 15px rgba(4,236,240,.1);">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="bg-theme bg-opacity-10 text-theme rounded p-3 me-3 fs-3">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 text-uppercase fs-11px fw-bold" style="letter-spacing: 1px;">Total Time Spent</h6>
                    <h3 class="fw-bold mb-0 text-inverse">
                        @if($totalTimeSeconds >= 3600)
                            {{ number_format($totalTimeSeconds / 3600, 1) }} <span class="fs-6 text-muted fw-normal">hr</span>
                        @else
                            {{ round($totalTimeSeconds / 60) }} <span class="fs-6 text-muted fw-normal">min</span>
                        @endif
                    </h3>
                </div>
            </div>
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
        </div>
    </div>
    
    <!-- Stat 2 -->
    <div class="col-md-4">
        <div class="card h-100 bg-dark border-secondary">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="bg-secondary bg-opacity-50 text-light rounded p-3 me-3 fs-3">
                    <i class="bi bi-collection-play"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 text-uppercase fs-11px fw-bold" style="letter-spacing: 1px;">Lessons Verified</h6>
                    <h3 class="fw-bold mb-0 text-inverse">
                        {{ $lessonProgress->where('is_completed', true)->count() }}
                    </h3>
                </div>
            </div>
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
        </div>
    </div>
    
    <!-- Stat 3 -->
    <div class="col-md-4">
        <div class="card h-100 bg-dark border-secondary">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="bg-success bg-opacity-10 text-success rounded p-3 me-3 fs-3">
                    <i class="bi bi-award"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 text-uppercase fs-11px fw-bold" style="letter-spacing: 1px;">Modules Certified</h6>
                    <h3 class="fw-bold mb-0 text-inverse">
                        {{ $completions->count() }}
                    </h3>
                </div>
            </div>
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
        </div>
    </div>
</div>

<h5 class="fw-bold text-inverse mb-3 mt-5"><i class="bi bi-clock me-2"></i>Recent Lesson Activity</h5>
<div class="card border-secondary">
    <div class="table-responsive">
        <table class="table table-dark table-hover table-striped table-bordered mb-0">
            <thead class="table-dark">
                <tr>
                    <th class="ps-3 border-secondary">Course</th>
                    <th class="border-secondary">Module</th>
                    <th class="border-secondary">Lesson</th>
                    <th class="border-secondary">Time Spent</th>
                    <th class="border-secondary">Status</th>
                    <th class="border-secondary text-end pe-3">Last Active / Completed</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lessonProgress as $lesson)
                    <tr class="border-secondary align-middle">
                        <td class="ps-3"><span class="badge bg-secondary text-inverse border border-gray">{{ $lesson->course_slug }}</span></td>
                        <td><code class="text-theme">{{ $lesson->module_slug }}</code></td>
                        <td class="fw-semibold">{{ $lesson->lesson_slug }}</td>
                        <td>
                            @if($lesson->time_spent_seconds >= 60)
                                {{ round($lesson->time_spent_seconds / 60) }}m {{ $lesson->time_spent_seconds % 60 }}s
                            @else
                                {{ $lesson->time_spent_seconds }}s
                            @endif
                        </td>
                        <td>
                            @if($lesson->is_completed)
                                <span class="badge bg-success bg-opacity-20 text-success d-inline-flex align-items-center rounded-pill px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i> Verified</span>
                            @else
                                <span class="badge bg-warning bg-opacity-20 text-warning d-inline-flex align-items-center rounded-pill px-2 py-1"><i class="bi bi-hourglass-split me-1"></i> In Progress</span>
                            @endif
                        </td>
                        <td class="pe-3 text-end text-muted small">
                            {{ $lesson->updated_at->format('M d, Y h:i A') }}
                        </td>
                    </tr>
                @empty
                    <tr class="border-secondary"><td colspan="6" class="text-center text-muted py-4">No lesson activity recorded yet for this user.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
</div>
@endsection
