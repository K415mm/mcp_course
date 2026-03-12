@extends('layouts.app')

@section('title', $user->name . ' — Profile')

@section('content')
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-theme">Dashboard</a></li>
            <li class="breadcrumb-item active">My Profile</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Left Sidebar: Avatar + Info -->
        <div class="col-xl-3 col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center p-4">
                    <!-- Avatar -->
                    <div class="mb-3">
                        <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="rounded-circle"
                            style="width:80px;height:80px;object-fit:cover;border:3px solid rgba(var(--bs-theme-rgb),.3);">
                    </div>
                    <h5 class="fw-bold mb-1 text-inverse">{{ $user->name }}</h5>
                    <div class="mb-2">
                        @if($user->isAdmin())
                            <span class="badge bg-danger">Admin</span>
                        @else
                            <span class="badge bg-theme text-dark">Student</span>
                        @endif
                    </div>
                    @if($user->job_title)
                        <div class="text-muted fs-13px mb-2">{{ $user->job_title }}</div>
                    @endif
                    @if($user->bio)
                        <p class="text-muted fs-12px mb-3">{{ $user->bio }}</p>
                    @endif
                    <div class="text-muted fs-12px mb-3">
                        <i class="bi bi-calendar me-1"></i>
                        Joined {{ $user->created_at->format('M Y') }}
                    </div>
                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-theme btn-sm w-100">
                        <i class="bi bi-pencil me-1"></i>Edit Profile
                    </a>
                </div>
                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>
            </div>

            <!-- Overall Stats -->
            <div class="card mt-3">
                <div class="card-body p-3">
                    @php
                        $totalModules = count($moduleProgress);
                        $startedMods = collect($moduleProgress)->filter(fn($m) => $m['seen'] > 0)->count();
                        $completedMods = collect($moduleProgress)->filter(fn($m) => $m['pct'] >= 100)->count();
                        $totalLessons = collect($moduleProgress)->sum('total');
                        $seenLessons = collect($moduleProgress)->sum('seen');
                    @endphp
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fs-12px text-muted">Overall Progress</span>
                        <span class="fs-12px fw-bold text-theme">
                            {{ $totalLessons > 0 ? (int) round($seenLessons / $totalLessons * 100) : 0 }}%
                        </span>
                    </div>
                    <div class="progress mb-3" style="height:6px;">
                        <div class="progress-bar bg-theme"
                            style="width:{{ $totalLessons > 0 ? round($seenLessons / $totalLessons * 100) : 0 }}%"></div>
                    </div>
                    <div class="row text-center g-0">
                        <div class="col-4 border-end border-secondary">
                            <div class="fw-bold text-inverse">{{ $startedMods }}</div>
                            <div class="fs-10px text-muted">Started</div>
                        </div>
                        <div class="col-4 border-end border-secondary">
                            <div class="fw-bold text-inverse">{{ $seenLessons }}</div>
                            <div class="fs-10px text-muted">Lessons</div>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-theme">{{ $completedMods }}</div>
                            <div class="fs-10px text-muted">Done</div>
                        </div>
                    </div>
                </div>
                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>
            </div>
        </div>

        <!-- Right Content: Module Progress -->
        <div class="col-xl-9 col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="fw-semibold mb-4 d-flex align-items-center gap-2">
                        <i class="bi bi-bar-chart-line text-theme"></i>
                        Course Progress
                    </h5>

                    @if(count($moduleProgress) === 0)
                        <p class="text-muted">No modules found.</p>
                    @else
                        <div class="row g-3">
                            @foreach($moduleProgress as $slug => $data)
                                @php $mod = $data['module']; @endphp
                                <div class="col-xl-6">
                                    <div class="card h-100" style="border-color:rgba(var(--bs-theme-rgb),.15);">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <span
                                                    class="badge bg-theme text-dark fs-10px">{{ sprintf('%02d', $mod['number']) }}</span>
                                                <a href="{{ route('course.module', $slug) }}"
                                                    class="fw-semibold fs-13px text-inverse text-decoration-none hover-text-theme">
                                                    {{ $mod['title'] }}
                                                </a>
                                            </div>
                                            <div class="d-flex justify-content-between fs-11px text-muted mb-1">
                                                <span>{{ $data['seen'] }} / {{ $data['total'] }} lessons</span>
                                                <span class="fw-bold {{ $data['pct'] >= 100 ? 'text-success' : 'text-theme' }}">
                                                    {{ $data['pct'] }}%
                                                </span>
                                            </div>
                                            <div class="progress" style="height:5px;">
                                                <div class="progress-bar {{ $data['pct'] >= 100 ? 'bg-success' : 'bg-theme' }}"
                                                    style="width:{{ $data['pct'] }}%;"></div>
                                            </div>
                                        </div>
                                        <div class="card-arrow">
                                            <div class="card-arrow-top-left"></div>
                                            <div class="card-arrow-top-right"></div>
                                            <div class="card-arrow-bottom-left"></div>
                                            <div class="card-arrow-bottom-right"></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>
            </div>

            <!-- Two Factor Authentication -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-shield-lock text-theme"></i>
                        Two-Factor Authentication
                    </h5>

                    @if(session('status') === 'two-factor-authentication-enabled')
                        <div class="alert alert-success fs-13px py-2 mb-3">
                            <i class="bi bi-check-circle me-2"></i>
                            Two factor authentication has been enabled. Please finish configuring it below.
                        </div>
                    @endif

                    @if(session('status') === 'two-factor-authentication-confirmed')
                        <div class="alert alert-success fs-13px py-2 mb-3">
                            <i class="bi bi-check-circle me-2"></i>
                            Two factor authentication has been confirmed and activated successfully.
                        </div>
                    @endif

                    @if(session('status') === 'two-factor-authentication-disabled')
                        <div class="alert alert-secondary fs-13px py-2 mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            Two factor authentication has been disabled.
                        </div>
                    @endif

                    <!-- Form Errors for 2FA confirmation -->
                    @if($errors->has('code'))
                        <div class="alert alert-danger fs-13px py-2 mb-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            {{ $errors->first('code') }}
                        </div>
                    @endif

                    @if(!$user->two_factor_secret)
                        <p class="text-muted fs-13px mb-3">
                            Add additional security to your account using two-factor authentication. When enabled, you will be
                            prompted for a secure, random token during authentication. You may retrieve this token from your
                            phone's Google Authenticator application.
                        </p>
                        <form method="POST" action="{{ route('two-factor.enable') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-theme btn-sm">Enable 2FA</button>
                        </form>

                    @elseif($user->two_factor_secret && !$user->two_factor_confirmed_at)
                        <div class="mb-4">
                            <p class="text-inverse fw-semibold fs-13px mb-2">
                                To finish enabling two factor authentication, scan the following QR code using your phone's
                                authenticator application and provide the generated code.
                            </p>
                            <div class="bg-white p-3 rounded d-inline-block mb-3">
                                {!! $qrCode !!}
                            </div>

                            <form method="POST" action="{{ route('two-factor.confirm') }}"
                                class="d-flex gx-2 align-items-center mb-3" style="max-width: 300px;">
                                @csrf
                                <input type="text" name="code" class="form-control form-control-sm me-2 bg-inverse bg-opacity-5"
                                    placeholder="6-digit code" required autofocus>
                                <button type="submit" class="btn btn-theme btn-sm text-dark px-3 mt-0">Confirm</button>
                            </form>
                        </div>

                        <form method="POST" action="{{ route('two-factor.disable') }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">Cancel</button>
                        </form>

                    @else
                        <p class="text-success fs-13px fw-semibold mb-3">
                            <i class="bi bi-check-circle-fill me-2"></i>You have enabled two-factor authentication.
                        </p>
                        <form method="POST" action="{{ route('two-factor.disable') }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">Disable 2FA</button>
                        </form>
                    @endif
                </div>
                <!-- Card arrows for UI theme -->
                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>
            </div>
        </div>
    </div>
@endsection