@extends('layouts.app')

@section('title', 'Edit Profile — ' . config('app.name'))

@section('content')
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-theme">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('profile') }}" class="text-theme">My Profile</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fs-13px mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-xl-7 col-lg-9">
            <div class="card">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 d-flex align-items-center gap-2">
                        <i class="bi bi-person-gear text-theme"></i> Edit Profile
                    </h5>

                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Avatar Preview + Upload -->
                        <div class="mb-4 text-center">
                            <img id="avatar-preview" src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}"
                                class="rounded-circle mb-2"
                                style="width:80px;height:80px;object-fit:cover;border:3px solid rgba(var(--bs-theme-rgb),.4);">
                            <div>
                                <label for="avatar" class="btn btn-sm btn-outline-theme">
                                    <i class="bi bi-camera me-1"></i>Change Photo
                                </label>
                                <input type="file" id="avatar" name="avatar" class="d-none" accept="image/*"
                                    onchange="previewAvatar(this)">
                                @error('avatar')
                                    <div class="text-danger fs-12px mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Name -->
                        <div class="mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                class="form-control bg-inverse bg-opacity-5 @error('name') is-invalid @enderror" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                class="form-control bg-inverse bg-opacity-5 @error('email') is-invalid @enderror" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Job Title -->
                        <div class="mb-3">
                            <label class="form-label">Job Title</label>
                            <input type="text" name="job_title" value="{{ old('job_title', $user->job_title) }}"
                                class="form-control bg-inverse bg-opacity-5" placeholder="e.g. Security Analyst">
                        </div>

                        <!-- Bio -->
                        <div class="mb-4">
                            <label class="form-label">Bio</label>
                            <textarea name="bio" rows="3"
                                class="form-control bg-inverse bg-opacity-5 @error('bio') is-invalid @enderror"
                                placeholder="Tell us about yourself...">{{ old('bio', $user->bio) }}</textarea>
                            @error('bio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <hr class="border-secondary my-4">
                        <h6 class="fw-semibold mb-3 text-muted">Change Password <small class="fs-11px">(leave blank to keep
                                current)</small></h6>

                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password"
                                class="form-control bg-inverse bg-opacity-5 @error('password') is-invalid @enderror"
                                placeholder="Min. 8 characters">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control bg-inverse bg-opacity-5"
                                placeholder="Repeat password">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-theme px-4">
                                <i class="bi bi-check2 me-1"></i>Save Changes
                            </button>
                            <a href="{{ route('profile') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => document.getElementById('avatar-preview').src = e.target.result;
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection