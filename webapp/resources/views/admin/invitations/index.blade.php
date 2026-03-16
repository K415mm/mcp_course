@extends('layouts.app')
@section('title', 'Invitations — Admin')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-theme">Admin</a></li>
                <li class="breadcrumb-item active">Invitations</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-inverse">
            <i class="bi bi-envelope-plus text-theme me-2"></i>User Invitations
        </h4>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible mb-4 fs-13px">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible mb-4 fs-13px">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    {{-- ── Send Single Invite ─────────────────────────────────────────────── --}}
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header bg-transparent border-bottom border-secondary">
                <h6 class="mb-0 text-inverse"><i class="bi bi-person-plus me-2 text-theme"></i>Send Invitation</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.invitations.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-muted fs-12px">Name (optional)</label>
                        <input type="text" name="name" class="form-control bg-dark border-secondary text-inverse"
                               placeholder="e.g. John Doe" value="{{ old('name') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted fs-12px">Email Address *</label>
                        <input type="email" name="email" class="form-control bg-dark border-secondary text-inverse"
                               placeholder="user@example.com" value="{{ old('email') }}" required>
                    </div>
                    <button type="submit" class="btn btn-theme w-100">
                        <i class="bi bi-send me-2"></i>Send Invitation
                    </button>
                </form>
            </div>
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
        </div>

        {{-- ── Bulk Invite ─────────────────────────────────────────────────── --}}
        <div class="card mt-4">
            <div class="card-header bg-transparent border-bottom border-secondary">
                <h6 class="mb-0 text-inverse"><i class="bi bi-people me-2 text-theme"></i>Bulk Invite</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.invitations.bulk') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-muted fs-12px">Paste Emails (one per line or comma-separated)</label>
                        <textarea name="emails" class="form-control bg-dark border-secondary text-inverse"
                                  rows="6" placeholder="alice@example.com&#10;bob@example.com&#10;carol@example.com"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted fs-12px">— or upload CSV file —</label>
                        <input type="file" name="csv_file" class="form-control bg-dark border-secondary text-muted" accept=".csv,.txt">
                    </div>
                    <button type="submit" class="btn btn-outline-theme w-100">
                        <i class="bi bi-send-fill me-2"></i>Send Bulk Invitations
                    </button>
                </form>
            </div>
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
        </div>
    </div>

    {{-- ── Invitation Table ───────────────────────────────────────────────── --}}
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header bg-transparent border-bottom border-secondary d-flex align-items-center justify-content-between">
                <h6 class="mb-0 text-inverse"><i class="bi bi-list-ul me-2 text-theme"></i>All Invitations</h6>
                <span class="badge bg-secondary">{{ $invitations->count() }} total</span>
            </div>
            <div class="card-body p-0">
                <table id="inviteTable" class="table table-hover mb-0 align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th class="px-3 py-3">Email</th>
                            <th>Status</th>
                            <th>Sent By</th>
                            <th>Expires</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invitations as $inv)
                            <tr>
                                <td class="px-3">
                                    <div class="fw-semibold text-inverse fs-13px">{{ $inv->email }}</div>
                                    @if($inv->name)<div class="text-muted fs-11px">{{ $inv->name }}</div>@endif
                                </td>
                                <td>
                                    @if($inv->isAccepted())
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Accepted</span>
                                    @elseif($inv->isExpired())
                                        <span class="badge bg-danger"><i class="bi bi-clock me-1"></i>Expired</span>
                                    @else
                                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass me-1"></i>Pending</span>
                                    @endif
                                </td>
                                <td class="text-muted fs-12px">{{ $inv->invitedBy?->name ?? '—' }}</td>
                                <td class="text-muted fs-12px">{{ $inv->expires_at?->diffForHumans() ?? '—' }}</td>
                                <td class="text-end pe-3">
                                    <div class="d-flex gap-1 justify-content-end">
                                        @unless($inv->isAccepted())
                                            {{-- Resend --}}
                                            <form method="POST" action="{{ route('admin.invitations.resend', $inv->id) }}">
                                                @csrf
                                                <button class="btn btn-xs btn-outline-theme" title="Resend">
                                                    <i class="bi bi-arrow-clockwise"></i>
                                                </button>
                                            </form>
                                        @endunless
                                        {{-- Copy link --}}
                                        <button class="btn btn-xs btn-outline-secondary copy-link"
                                                data-link="{{ url('/invite/'.$inv->token) }}" title="Copy invite link">
                                            <i class="bi bi-link-45deg"></i>
                                        </button>
                                        {{-- Revoke --}}
                                        <form method="POST" action="{{ route('admin.invitations.destroy', $inv->id) }}"
                                              onsubmit="return confirm('Revoke this invitation?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-xs btn-outline-danger" title="Revoke">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="bi bi-envelope-open" style="font-size:2.5rem;opacity:.25;"></i>
                                    <p class="mt-2">No invitations sent yet.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Copy invite link to clipboard
document.querySelectorAll('.copy-link').forEach(btn => {
    btn.addEventListener('click', () => {
        navigator.clipboard.writeText(btn.dataset.link).then(() => {
            const old = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2"></i>';
            setTimeout(() => btn.innerHTML = old, 1500);
        });
    });
});
</script>
<style>.btn-xs { padding:.2rem .5rem; font-size:.75rem; }</style>
@endpush
