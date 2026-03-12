@extends('layouts.app')

@section('title', 'Media Library — Admin')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb" class="mb-1">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-theme">Admin</a></li>
                    <li class="breadcrumb-item active">Media</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-inverse"><i class="bi bi-images text-theme me-2"></i>Media Library</h4>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fs-13px mb-4"><i
                class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close"
                data-bs-dismiss="alert"></button></div>
    @endif

    <!-- Upload Zone -->
    <div class="card mb-4">
        <div class="card-body p-4">
            <h6 class="fw-semibold mb-3"><i class="bi bi-cloud-upload text-theme me-2"></i>Upload Files</h6>
            <div id="dropzone"
                style="border:2px dashed rgba(var(--bs-theme-rgb),.4);border-radius:12px;padding:2.5rem;text-align:center;cursor:pointer;transition:.2s;background:rgba(var(--bs-theme-rgb),.03);"
                ondragover="this.style.borderColor='var(--bs-theme)';event.preventDefault()"
                ondragleave="this.style.borderColor='rgba(var(--bs-theme-rgb),.4)'" ondrop="handleDrop(event)"
                onclick="document.getElementById('fileInput').click()">
                <i class="bi bi-cloud-upload"
                    style="font-size:2.5rem;color:var(--bs-theme);display:block;margin-bottom:.75rem;"></i>
                <div class="fw-semibold mb-1 text-inverse">Drag &amp; drop or click to upload</div>
                <div class="fs-12px text-muted">Images, videos, documents — max 50MB each</div>
            </div>
            <input type="file" id="fileInput" multiple class="d-none" onchange="uploadFiles(this.files)">
            <div id="uploadProgress" class="mt-3"></div>
        </div>
        <div class="card-arrow">
            <div class="card-arrow-top-left"></div>
            <div class="card-arrow-top-right"></div>
            <div class="card-arrow-bottom-left"></div>
            <div class="card-arrow-bottom-right"></div>
        </div>
    </div>

    <!-- Media Grid -->
    <div class="row g-3" id="mediaGrid">
        @foreach($media as $item)
            <div class="col-xl-2 col-lg-3 col-md-4 col-6" id="media-{{ $item->id }}">
                <div class="card h-100">
                    <div class="card-body p-2 text-center">
                        <!-- Thumbnail -->
                        @if($item->isImage())
                            <img src="{{ $item->url() }}" alt="{{ $item->original_name }}" class="img-fluid rounded mb-2"
                                style="max-height:120px;object-fit:cover;width:100%;">
                        @elseif($item->isVideo())
                            <div class="d-flex align-items-center justify-content-center mb-2"
                                style="height:120px;background:#1a1d23;border-radius:6px;">
                                <i class="bi bi-play-circle" style="font-size:2.5rem;color:var(--bs-theme);"></i>
                            </div>
                        @else
                            <div class="d-flex align-items-center justify-content-center mb-2"
                                style="height:120px;background:#1a1d23;border-radius:6px;">
                                <i class="bi bi-file-earmark" style="font-size:2.5rem;color:#6c757d;"></i>
                            </div>
                        @endif
                        <!-- Info -->
                        <div class="fs-11px text-inverse text-truncate" title="{{ $item->original_name }}">
                            {{ $item->original_name }}</div>
                        <div class="fs-10px text-muted">{{ $item->humanSize() }}</div>
                        <!-- Actions -->
                        <div class="d-flex gap-1 justify-content-center mt-2">
                            <button class="btn btn-xs btn-outline-theme" title="Copy URL"
                                onclick="copyUrl('{{ $item->url() }}')"><i class="bi bi-clipboard"></i></button>
                            <a href="{{ $item->url() }}" target="_blank" class="btn btn-xs btn-outline-secondary"
                                title="View"><i class="bi bi-eye"></i></a>
                            <form method="POST" action="{{ route('admin.media.destroy', $item) }}" class="d-inline"
                                onsubmit="return confirm('Delete this file?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
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

    @if($media->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-images" style="font-size:3rem;display:block;margin-bottom:1rem;opacity:.3;"></i>
            No media uploaded yet. Use the upload zone above.
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        const uploadUrl = '{{ route('admin.media.upload') }}';
        const csrfToken = '{{ csrf_token() }}';

        function uploadFiles(files) {
            [...files].forEach(file => {
                const fd = new FormData();
                fd.append('file', file);
                fd.append('_token', csrfToken);
                const bar = document.createElement('div');
                bar.className = 'mb-2 fs-12px text-muted';
                bar.innerHTML = `<i class="bi bi-arrow-up-circle me-1 text-theme"></i>Uploading <b>${file.name}</b>...`;
                document.getElementById('uploadProgress').appendChild(bar);
                fetch(uploadUrl, { method: 'POST', body: fd })
                    .then(r => r.json()).then(data => {
                        bar.innerHTML = `<i class="bi bi-check-circle me-1 text-success"></i><b>${data.original}</b> — <a href="${data.url}" target="_blank" class="text-theme">${data.url}</a>`;
                        location.reload();
                    }).catch(() => { bar.innerHTML = '<i class="bi bi-x-circle me-1 text-danger"></i>Upload failed.'; });
            });
        }

        function handleDrop(e) {
            e.preventDefault();
            document.getElementById('dropzone').style.borderColor = 'rgba(var(--bs-theme-rgb),.4)';
            uploadFiles(e.dataTransfer.files);
        }

        function copyUrl(url) {
            navigator.clipboard.writeText(url).then(() => alert('URL copied!\n' + url));
        }
    </script>
    <style>
        .btn-xs {
            padding: .2rem .5rem;
            font-size: .75rem;
        }
    </style>
@endpush