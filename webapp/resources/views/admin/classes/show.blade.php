@extends('layouts.app')

@section('title', 'Class: ' . $class->name)

@section('content')
<div class="d-flex align-items-center mb-3">
    <div>
        <h1 class="page-header mb-0">{{ $class->name }}</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.classes.index') }}">Classes</a></li>
            <li class="breadcrumb-item active">{{ $class->name }}</li>
        </ol>
    </div>
</div>

<div class="row">
    {{-- Class Details & Settings --}}
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header bg-transparent border-bottom border-white border-opacity-10 fw-semibold d-flex align-items-center">
                <i class="bi bi-gear me-2"></i> Class Settings
            </div>
            <div class="card-body">
                <form action="{{ route('admin.classes.update', $class->id) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Class Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $class->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" value="{{ $class->description }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Year</label>
                        <input type="text" name="year" class="form-control" value="{{ $class->year }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" {{ $class->status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="archived" {{ $class->status === 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-theme w-100">Save Changes</button>
                </form>
            </div>
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
        </div>

        {{-- Course Enrollments for this Class --}}
        <div class="card mt-4">
            <div class="card-header bg-transparent border-bottom border-white border-opacity-10 fw-semibold d-flex align-items-center">
                <i class="bi bi-book-half me-2"></i> Enrolled Courses
            </div>
            <div class="card-body">
                @if(count($enrolledCourses) > 0)
                    <ul class="list-group list-group-flush mb-3">
                        @foreach($enrolledCourses as $courseSlug)
                            @php
                                $c = collect($allCourses)->firstWhere('slug', $courseSlug);
                            @endphp
                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <i class="bi bi-journal-code text-theme me-2"></i>
                                    {{ $c['title'] ?? $courseSlug }}
                                </div>
                                <form action="{{ route('admin.courses.unassignClasses') }}" method="POST" onsubmit="return confirm('Remove course from this class? Students will lose access.');">
                                    @csrf
                                    <input type="hidden" name="course_slug" value="{{ $courseSlug }}">
                                    <input type="hidden" name="class_id" value="{{ $class->id }}">
                                    <button type="submit" class="btn btn-link text-danger p-0"><i class="bi bi-x-circle"></i></button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted small">No courses assigned to this class yet.</p>
                @endif
                
                <hr class="border-white border-opacity-10">
                <form action="{{ route('admin.courses.assignClasses') }}" method="POST">
                    @csrf
                    <input type="hidden" name="class_id" value="{{ $class->id }}">
                    <div class="input-group">
                        <select name="course_slug" class="form-select form-select-sm" required>
                            <option value="">Select Course...</option>
                            @foreach($allCourses as $c)
                                @if(!in_array($c['slug'], $enrolledCourses))
                                    <option value="{{ $c['slug'] }}">{{ $c['title'] }}</option>
                                @endif
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-outline-theme btn-sm">Assign</button>
                    </div>
                </form>
            </div>
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
        </div>
    </div>

    {{-- Students List --}}
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header bg-transparent border-bottom border-white border-opacity-10 fw-semibold d-flex align-items-center justify-content-between">
                <div><i class="bi bi-people me-2"></i> Class Students <span class="badge bg-secondary ms-2">{{ $class->students->count() }}</span></div>
                
                <form action="{{ route('admin.classes.addStudent', $class->id) }}" method="POST" class="d-flex align-items-center">
                    @csrf
                    <select name="user_id" class="form-select form-select-sm me-2" required style="width: 200px;">
                        <option value="">Add student...</option>
                        @foreach($availableUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-outline-theme btn-sm">Add</button>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover text-nowrap align-middle mb-0 m-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">Student Name</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($class->students as $student)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $student->avatarUrl() }}" class="rounded-circle me-2" width="24" height="24" alt="">
                                            {{ $student->name }}
                                        </div>
                                    </td>
                                    <td>{{ $student->email }}</td>
                                    <td>
                                        <form action="{{ route('admin.classes.removeStudent', [$class->id, $student->id]) }}" method="POST" onsubmit="return confirm('Remove student from this class?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-5">No students added to this class yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
        </div>
    </div>
</div>
@endsection
