<?php

namespace App\Services;

use App\Models\MediaLibrary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MediaService
{
    public function upload(UploadedFile $file): MediaLibrary
    {
        $path = $file->store('media', 'public');  // storage/app/public/media/
        return MediaLibrary::create([
            'filename' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'disk' => 'public',
            'uploaded_by' => Auth::id(),
        ]);
    }

    public function getAllMedia(): \Illuminate\Database\Eloquent\Collection
    {
        return MediaLibrary::with('uploader')->orderByDesc('created_at')->get();
    }

    public function delete(MediaLibrary $media): void
    {
        Storage::disk($media->disk)->delete($media->filename);
        $media->delete();
    }

    public function publicUrl(MediaLibrary $media): string
    {
        return $media->url();
    }
}
