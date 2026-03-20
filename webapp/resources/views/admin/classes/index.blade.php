@extends('layouts.app')

@section('title', 'Admin - Student Classes')

@section('content')
<div class="d-flex align-items-center mb-3">
    <div>
        <h1 class="page-header mb-0">Student Classes</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Classes</li>
        </ol>
    </div>
    <div class="ms-auto">
        <button class="btn btn-outline-theme btn-sm" data-bs-toggle="modal" data-bs-target="#createClassModal">
            <i class="bi bi-plus-lg me-1"></i> New Class
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover text-nowrap align-middle mb-0">
                <thead>
                    <tr>
                        <th>Class Name</th>
                        <th>Year</th>
                        <th>Students</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classes as $class)
                        <tr>
                            <td>
                                <a href="{{ route('admin.classes.show', $class->id) }}" class="fw-semibold text-inverse text-decoration-none">
                                    {{ $class->name }}
                                </a>
                                @if($class->description)
                                    <div class="small text-muted">{{ $class->description }}</div>
                                @endif
                            </td>
                            <td>{{ $class->year ?? '-' }}</td>
                            <td><span class="badge bg-secondary">{{ $class->students_count }}</span></td>
                            <td>
                                @if($class->isActive())
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Archived</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.classes.show', $class->id) }}" class="btn btn-sm btn-outline-info">Manage</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No student classes created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
</div>

{{-- Create Modal --}}
<div class="modal fade" id="createClassModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.classes.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Create New Class</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Class Name *</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Cohort 2025-A">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description (optional)</label>
                    <input type="text" name="description" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Year</label>
                    <input type="text" name="year" class="form-control" placeholder="2025">
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-outline-theme">Create Class</button>
            </div>
        </form>
    </div>
</div>
@endsection
