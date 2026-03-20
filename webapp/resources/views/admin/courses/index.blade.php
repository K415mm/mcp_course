@extends('layouts.app')

@section('title', 'Admin - Courses Assignment')

@section('content')
<div class="d-flex align-items-center mb-3">
    <div>
        <h1 class="page-header mb-0">Course Enrollment Management</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Courses</li>
        </ol>
    </div>
</div>

<div class="row">
    @foreach($courses as $course)
        <div class="col-xl-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-transparent border-bottom border-white border-opacity-10 d-flex justify-content-between align-items-center">
                    <div class="fw-bold fs-5 text-theme">{{ $course['title'] }}</div>
                    <div>
                        <span class="badge bg-secondary" title="Total Enrolled Students (Direct)">
                            <i class="bi bi-person me-1"></i> {{ $course['direct_enrollments'] }} 
                        </span>
                        <span class="badge bg-info" title="Total Enrolled Classes">
                            <i class="bi bi-collection me-1"></i> {{ $course['class_enrollments'] }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-4">{{ $course['description'] ?? 'No description.' }}</p>
                    
                    <div class="row">
                        <div class="col-md-6 border-end border-white border-opacity-10">
                            <h6 class="mb-3">Assign to Class</h6>
                            <form action="{{ route('admin.courses.assignClasses') }}" method="POST">
                                @csrf
                                <input type="hidden" name="course_slug" value="{{ $course['slug'] }}">
                                <div class="mb-2">
                                    <select name="class_id" class="form-select form-select-sm" required>
                                        <option value="">Select a class...</option>
                                        @foreach($classes as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-sm btn-outline-theme w-100">Enroll Class</button>
                            </form>
                        </div>
                        <div class="col-md-6 ps-md-3 mt-3 mt-md-0">
                            <h6 class="mb-3">Assign to Student (Direct)</h6>
                            <form action="{{ route('admin.courses.assignStudent') }}" method="POST">
                                @csrf
                                <input type="hidden" name="course_slug" value="{{ $course['slug'] }}">
                                <div class="mb-2">
                                    <select name="user_id" class="form-select form-select-sm" required>
                                        <option value="">Select a student...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-sm btn-outline-theme w-100">Enroll Student</button>
                            </form>
                        </div>
                    </div>

                </div>
                <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
            </div>
        </div>
    @endforeach
</div>
@endsection
