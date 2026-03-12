@extends('layouts.app')

@section('title', 'Users — Admin')

@push('head')
    <link rel="stylesheet" href="{{ asset('hud/plugins/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
@endpush

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb" class="mb-1">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-theme">Admin</a></li>
                    <li class="breadcrumb-item active">Users</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-inverse"><i class="bi bi-people text-theme me-2"></i>User Management</h4>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fs-13px mb-4"><i
                class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close"
                data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table id="usersTable" class="table table-hover mb-0 align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th class="px-3 py-3">User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Progress</th>
                        <th>Joined</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td class="px-3">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $user->avatarUrl() }}" alt="" class="rounded-circle"
                                        style="width:32px;height:32px;object-fit:cover;">
                                    <div>
                                        <div class="fw-semibold text-inverse fs-13px">{{ $user->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="fs-13px text-muted">{{ $user->email }}</td>
                            <td>
                                <span
                                    class="badge {{ $user->roleBadgeClass() }} fs-10px px-2 py-1">{{ $user->roleLabel() }}</span>
                            </td>
                            <td>
                                @php $seen = count($user->modules_viewed ?? []); @endphp
                                <span class="fs-12px text-muted">{{ $seen }} lesson{{ $seen === 1 ? '' : 's' }} completed</span>
                            </td>
                            <td class="fs-12px text-muted">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="text-end pe-3">
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.role', $user) }}"
                                        class="d-flex align-items-center justify-content-end gap-2">
                                        @csrf
                                        <select name="role"
                                            class="form-select form-select-sm w-auto bg-dark border-secondary fs-12px"
                                            onchange="this.form.submit()">
                                            @foreach(\App\Models\User::ROLES as $role)
                                                                    <option value="{{ $role }}" {{ $user->role === $role ? 'selected' : '' }}>
                                                                        {{ match ($role) {
                                                    'guest' => 'Guest',
                                                    'preenrol' => 'Pre-Enrolled',
                                                    'student' => 'Student',
                                                    'cstudent' => 'Certified Student',
                                                    'admin' => 'Admin',
                                                    default => ucfirst($role)
                                                } }}
                                                                    </option>
                                            @endforeach
                                        </select>
                                    </form>
                                @else
                                    <span class="fs-12px text-muted">You</span>
                                @endif
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
    <script src="{{ asset('hud/plugins/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('hud/plugins/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script>$(document).ready(() => $('#usersTable').DataTable({ pageLength: 25 }));</script>
    <style>
        .btn-xs {
            padding: .2rem .5rem;
            font-size: .75rem;
        }
    </style>
@endpush