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
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fs-13px mb-4"><i
                class="bi bi-x-circle me-2"></i>{{ session('error') }}<button type="button" class="btn-close"
                data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fs-13px mb-4">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table id="usersTable" class="table table-hover mb-0 align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th class="px-3 py-3">User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr class="{{ $user->banned_at ? 'opacity-50' : '' }}">
                            <td class="px-3">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $user->avatarUrl() }}" alt="" class="rounded-circle"
                                        style="width:32px;height:32px;object-fit:cover; {{ $user->banned_at ? 'filter: grayscale(1);' : '' }}">
                                    <div>
                                        <div class="fw-semibold text-inverse fs-13px">
                                            {{ $user->name }}
                                            @if($user->banned_at)
                                                <i class="bi bi-slash-circle text-danger ms-1" title="Banned"></i>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="fs-13px text-muted">{{ $user->email }}</td>
                            <td>
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.role', $user) }}">
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
                                    <span class="badge {{ $user->roleBadgeClass() }} fs-10px px-2 py-1">{{ $user->roleLabel() }}</span>
                                @endif
                            </td>
                            <td>
                                @if($user->banned_at)
                                    <span class="badge bg-danger fs-10px px-2 py-1">Banned</span>
                                @elseif($user->email_verified_at)
                                    <span class="badge border border-success text-success fs-10px px-2 py-1">Verified</span>
                                @else
                                    <span class="badge border border-warning text-warning fs-10px px-2 py-1">Unverified</span>
                                @endif
                            </td>
                            <td class="fs-12px text-muted">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="text-end pe-3">
                                <div class="btn-group">
                                    {{-- Edit User Modal Trigger --}}
                                    <button class="btn btn-xs btn-outline-theme" data-bs-toggle="modal" data-bs-target="#editUserModal-{{ $user->id }}" title="Edit Details">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    
                                    {{-- View Progress --}}
                                    <a href="{{ route('admin.users.progress', $user) }}" class="btn btn-xs btn-outline-info" title="View Progress">
                                        <i class="bi bi-clock-history"></i>
                                    </a>
                                    
                                    {{-- Reset Password Modal Trigger --}}
                                    <button class="btn btn-xs btn-outline-warning" data-bs-toggle="modal" data-bs-target="#pwdUserModal-{{ $user->id }}" title="Reset Password">
                                        <i class="bi bi-key"></i>
                                    </button>

                                    @if($user->id !== auth()->id())
                                        {{-- Toggle Ban --}}
                                        <form method="POST" action="{{ route('admin.users.toggleBan', $user) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-outline-{{ $user->banned_at ? 'success' : 'danger' }}" title="{{ $user->banned_at ? 'Unban User' : 'Ban User' }}">
                                                <i class="bi bi-{{ $user->banned_at ? 'check-circle' : 'slash-circle' }}"></i>
                                            </button>
                                        </form>

                                        {{-- Delete User --}}
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('WARNING: This will permanently delete this user and all their progress. Continue?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete Account">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
    </div>
{{-- Edit Modal --}}
                                <div class="modal fade" id="editUserModal-{{ $user->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content bg-dark border-secondary text-start">
                                            <div class="modal-header border-bottom border-secondary">
                                                <h6 class="modal-title text-inverse"><i class="bi bi-pencil-square me-2 text-theme"></i>Edit User: {{ $user->name }}</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label text-muted fs-12px">Full Name</label>
                                                        <input type="text" name="name" class="form-control bg-dark border-secondary text-inverse" value="{{ $user->name }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label text-muted fs-12px">Email Address</label>
                                                        <input type="email" name="email" class="form-control bg-dark border-secondary text-inverse" value="{{ $user->email }}" required>
                                                    </div>

                                                    <hr class="border-secondary my-4">
                                                    <h6 class="text-theme fs-13px text-uppercase fw-bold mb-3"><i class="bi bi-shield-check me-2"></i>Entitlements Overrides</h6>
                                                    <p class="text-muted fs-12px mb-3">Leave inputs blank to inherit default capabilities from the user's role.</p>

                                                    <div class="mb-3">
                                                        <label class="form-label text-muted fs-12px">Max Enrolled Courses Override</label>
                                                        <input type="number" name="caps[max_courses]" class="form-control bg-dark border-secondary text-inverse" value="{{ is_array($user->capabilities) && array_key_exists('max_courses', $user->capabilities) ? $user->capabilities['max_courses'] : '' }}" placeholder="e.g. 5, or -1 for unlimited">
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label text-muted fs-12px">Workshops Override</label>
                                                        <select name="caps[workshops_enabled]" class="form-select bg-dark border-secondary text-inverse">
                                                            <option value="">-- Use Role Default --</option>
                                                            <option value="1" {{ is_array($user->capabilities) && array_key_exists('workshops_enabled', $user->capabilities) && $user->capabilities['workshops_enabled'] ? 'selected' : '' }}>Force Enabled</option>
                                                            <option value="0" {{ is_array($user->capabilities) && array_key_exists('workshops_enabled', $user->capabilities) && !$user->capabilities['workshops_enabled'] ? 'selected' : '' }}>Force Disabled</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top border-secondary">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-theme">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Password Modal --}}
                                <div class="modal fade" id="pwdUserModal-{{ $user->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content bg-dark border-secondary text-start">
                                            <div class="modal-header border-bottom border-secondary">
                                                <h6 class="modal-title text-inverse"><i class="bi bi-key me-2 text-warning"></i>Reset Password: {{ $user->name }}</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="{{ route('admin.users.password', $user) }}">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label text-muted fs-12px">New Password</label>
                                                        <input type="password" name="password" class="form-control bg-dark border-secondary text-inverse" placeholder="Min 8 characters" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label text-muted fs-12px">Confirm Password</label>
                                                        <input type="password" name="password_confirmation" class="form-control bg-dark border-secondary text-inverse" placeholder="Repeat password" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top border-secondary">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-warning text-dark fw-bold">Reset Password</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>


@endsection

@push('scripts')
    <script src="{{ asset('hud/plugins/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('hud/plugins/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script>$(document).ready(() => $('#usersTable').DataTable({ pageLength: 25 }));</script>
    <style>
        .btn-xs { padding: .2rem .5rem; font-size: .75rem; }
    </style>
@endpush
