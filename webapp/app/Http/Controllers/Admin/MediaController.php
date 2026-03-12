<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaLibrary;
use App\Services\MediaService;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(protected MediaService $mediaService)
    {
    }

    public function index()
    {
        $media = $this->mediaService->getAllMedia();
        return view('admin.media.index', compact('media'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // 50MB max
        ]);

        $media = $this->mediaService->upload($request->file('file'));

        // Return JSON for BlueImp AJAX handler
        return response()->json([
            'id' => $media->id,
            'url' => $media->url(),
            'original' => $media->original_name,
            'size' => $media->human_size ?? $media->humanSize(),
            'mime' => $media->mime_type,
            'deleteUrl' => route('admin.media.destroy', $media),
            'deleteType' => 'DELETE',
        ]);
    }

    public function destroy(MediaLibrary $media)
    {
        $this->mediaService->delete($media);

        if (request()->expectsJson()) {
            return response()->json(['deleted' => true]);
        }
        return redirect()->route('admin.media.index')->with('success', 'Media deleted.');
    }
}
