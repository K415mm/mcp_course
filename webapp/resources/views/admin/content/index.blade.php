@extends('layouts.app')

@section('title', 'Content Manager — Admin')

@push('head')
    <link rel="stylesheet" href="{{ asset('hud/plugins/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
@endpush

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb" class="mb-1">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-theme">Admin</a></li>
                    <li class="breadcrumb-item active">Content</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-inverse"><i class="bi bi-file-earmark-text text-theme me-2"></i>Content Manager
            </h4>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('admin.content.bulkPublish') }}" method="POST"
                onsubmit="return confirm('Publish all draft files?')">
                @csrf
                <button type="submit" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-check2-all me-1"></i>Publish All
                </button>
            </form>
            <a href="{{ route('admin.content.create') }}" class="btn btn-outline-theme btn-sm">
                <i class="bi bi-plus-circle me-1"></i>New Lesson
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fs-13px mb-4">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fs-13px mb-4">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Error:</strong> {{ $errors->first('error') ?: $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table id="contentTable" class="table table-hover mb-0 align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th class="px-3 py-3">Module</th>
                        <th>Section</th>
                        <th>Lesson File</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entries as $e)
                        <tr>
                            <td class="px-3">
                                <span class="fw-semibold text-inverse fs-13px">{{ $e['module'] }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary text-uppercase fs-10px">{{ $e['section'] }}</span>
                            </td>
                            <td>
                                <span class="fs-13px text-muted">{{ $e['file'] }}</span>
                            </td>
                            <td>
                                @php $typeColors = ['lesson' => 'bg-info', 'quiz' => 'bg-warning', 'video' => 'bg-danger', 'workshop' => 'bg-success', 'slides' => 'bg-purple']; @endphp
                                <span
                                    class="badge {{ $typeColors[$e['type']] ?? 'bg-secondary' }} fs-10px">{{ $e['type'] }}</span>
                            </td>
                            <td>
                                @if($e['status'] === 'published')
                                    <span class="badge bg-success text-dark fs-10px px-2 py-1"><i
                                            class="bi bi-globe me-1"></i>Published</span>
                                @else
                                    <span class="badge bg-secondary text-dark fs-10px px-2 py-1"><i
                                            class="bi bi-eye-slash me-1"></i>Draft</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-flex gap-1 justify-content-end align-items-center">
                                    <!-- Toggle Status -->
                                    <form method="POST" action="{{ route('admin.content.toggleStatus') }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="path" value="{{ $e['path'] }}">
                                        @if($e['status'] === 'published')
                                            <input type="hidden" name="status" value="draft">
                                            <button class="btn btn-xs btn-outline-warning" title="Unpublish (Set to Draft)"><i
                                                    class="bi bi-eye-slash"></i></button>
                                        @else
                                            <input type="hidden" name="status" value="published">
                                            <button class="btn btn-xs btn-outline-success" title="Publish"><i
                                                    class="bi bi-globe"></i></button>
                                        @endif
                                    </form>
                                    <!-- View -->
                                    <a href="{{ route('course.lesson', [$e['module_slug'], $e['section'], $e['lesson_slug']]) }}"
                                        class="btn btn-xs btn-outline-secondary" title="View"><i class="bi bi-eye"></i></a>
                                    <!-- Edit -->
                                    <a href="{{ route('admin.content.edit', [$e['module_slug'], $e['lesson_slug']]) }}"
                                        class="btn btn-xs btn-outline-theme" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <!-- Quiz Builder (only if type=quiz) -->
                                    @if($e['type'] === 'quiz')
                                        <a href="{{ route('admin.quiz.edit', [$e['module_slug'], $e['lesson_slug']]) }}"
                                            class="btn btn-xs btn-outline-warning" title="Edit Quiz"><i
                                                class="bi bi-question-circle"></i></a>
                                    @endif
                                    <!-- Delete -->
                                    @if($e['path'])
                                        <form method="POST" action="{{ route('admin.content.destroy') }}"
                                            onsubmit="return confirm('Delete {{ $e['file'] }}? This cannot be undone.')">
                                            @csrf @method('DELETE')
                                            <input type="hidden" name="path" value="{{ $e['path'] }}">
                                            <button class="btn btn-xs btn-outline-danger" title="Delete"><i
                                                    class="bi bi-trash"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-arrow">
            <div class="card-arrow-top-left"></div>
            <div class="card-arrow-top-right"></div>
            <div class="card-arrow-bottom-left"></div>
            <div class="card-arrow-bottom-right"></div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('hud/plugins/datatables.net/js/dataTables.min.js') }}"></script>
    <script src="{{ asset('hud/plugins/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('#contentTable').DataTable({ pageLength: 25, order: [[0, 'asc']] });
        });
    </script>
    <style>
        .btn-xs {
            padding: .2rem .5rem;
            font-size: .75rem;
        }
    </style>
@endpush