{{-- VIDEO CARD PARTIAL --}}
{{-- Expects: $meta (array with video, thumbnail, title), $contentHtml --}}

@php
    $videoSrc = $meta['video'] ?? '';
    $thumbnail = $meta['thumbnail'] ?? '';
    $videoTitle = $meta['title'] ?? ($lessonTitle ?? 'Video Lesson');
    $isYoutube = str_contains($videoSrc, 'youtube') || str_contains($videoSrc, 'youtu.be');
    $isVimeo = str_contains($videoSrc, 'vimeo');
    $isEmbed = $isYoutube || $isVimeo;
@endphp

@if(!$videoSrc)
    <div class="alert alert-warning">
        <i class="bi bi-camera-video me-2"></i>
        This lesson is marked as <strong>video</strong> but no video URL has been set.
        @if(Auth::user()->isAdmin())
            <a href="{{ route('admin.content.edit', [$module['slug'], $lessonSlug]) }}" class="alert-link ms-2">Edit Lesson</a>
        @endif
    </div>
@else

    {{-- Video Card - HUD Card Widget Style --}}
    <div class="card mb-4" style="overflow:hidden;">
        {{-- Thumbnail / Embed --}}
        <div class="position-relative" style="background:#000;">
            @if($isEmbed)
                {{-- Embedded YouTube/Vimeo in full width --}}
                <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;">
                    <iframe src="{{ $videoSrc }}" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"
                        allow="accelerometer;autoplay;clipboard-write;encrypted-media;gyroscope;picture-in-picture"
                        allowfullscreen title="{{ $videoTitle }}"></iframe>
                </div>
            @else
                {{-- Self-hosted video with Lity lightbox on click --}}
                <a href="{{ $videoSrc }}" data-lity class="d-block position-relative">
                    @if($thumbnail)
                        <img src="{{ $thumbnail }}" alt="{{ $videoTitle }}" class="w-100"
                            style="max-height:420px;object-fit:cover;">
                    @else
                        <div class="d-flex align-items-center justify-content-center" style="height:300px;background:#0d1117;">
                            <i class="bi bi-play-circle" style="font-size:5rem;color:var(--bs-theme);"></i>
                        </div>
                    @endif
                    {{-- Play Button Overlay --}}
                    <div class="position-absolute top-50 start-50 translate-middle"
                        style="width:72px;height:72px;background:rgba(var(--bs-theme-rgb),.9);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-play-fill" style="font-size:2rem;color:#000;margin-left:4px;"></i>
                    </div>
                </a>
            @endif
        </div>
        {{-- Card Info --}}
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-danger fs-11px"><i class="bi bi-camera-video me-1"></i>Video</span>
                <h5 class="fw-bold text-inverse mb-0">{{ $videoTitle }}</h5>
            </div>
            @if($contentHtml && trim(strip_tags($contentHtml)))
                <div class="md-content text-muted fs-14px">{!! $contentHtml !!}</div>
            @endif
            @if(!$isEmbed)
                <a href="{{ $videoSrc }}" class="btn btn-outline-theme btn-sm mt-3" data-lity>
                    <i class="bi bi-fullscreen me-1"></i>Watch Fullscreen
                </a>
            @endif
        </div>
        <div class="card-arrow">
            <div class="card-arrow-top-left"></div>
            <div class="card-arrow-top-right"></div>
            <div class="card-arrow-bottom-left"></div>
            <div class="card-arrow-bottom-right"></div>
        </div>
    </div>
@endif

@push('head')
    <link rel="stylesheet" href="{{ asset('hud/plugins/lity/dist/lity.min.css') }}">
@endpush
@push('scripts')
    <script src="{{ asset('hud/plugins/lity/dist/lity.min.js') }}"></script>
@endpush