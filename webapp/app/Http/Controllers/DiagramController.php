<?php

namespace App\Http\Controllers;

use App\Models\Diagram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DiagramController extends Controller
{
    /**
     * List diagrams.
     */
    public function index()
    {
        $query = Diagram::query();
        if (Auth::user()->isAdmin()) {
            $query->where('user_id', Auth::id());
        } else {
            $query->where('is_published', true);
        }
        $diagrams = $query->orderBy('updated_at', 'desc')->get();
        return view('diagrams.index', compact('diagrams'));
    }

    /**
     * Show the draw.io editor for creating a new diagram.
     */
    public function create()
    {
        abort_if(!Auth::user()->isAdmin(), 403, 'Only admins can create diagrams.');
        return view('diagrams.editor', ['diagram' => null]);
    }

    /**
     * Show the draw.io editor for an existing diagram.
     */
    public function edit(Diagram $diagram)
    {
        abort_if(!Auth::user()->isAdmin() || $diagram->user_id !== Auth::id(), 403);
        
        // Load the XML file from storage so the editor can re-open it
        $xmlContent = null;
        if ($diagram->file_path && Storage::disk('local')->exists($diagram->file_path)) {
            $xmlContent = Storage::disk('local')->get($diagram->file_path);
        } elseif ($diagram->xml_data) {
            // fallback to old db blob
            $xmlContent = $diagram->xml_data;
        }
        
        return view('diagrams.editor', compact('diagram', 'xmlContent'));
    }

    /**
     * Store a brand new diagram — saves the XML as a .drawio file.
     */
    public function store(Request $request)
    {
        abort_if(!Auth::user()->isAdmin(), 403);

        $request->validate([
            'title'       => 'nullable|string|max:255',
            'xml_data'    => 'required|string',
            'module_slug' => 'nullable|string|max:255',
        ]);

        // Save the XML to a file
        $filename = 'diagrams/' . Str::uuid() . '.drawio';
        Storage::disk('local')->put($filename, $request->input('xml_data'));

        $diagram = Diagram::create([
            'user_id'     => Auth::id(),
            'title'       => $request->input('title', 'Untitled Diagram'),
            'xml_data'    => null, // no longer used for new diagrams
            'file_path'   => $filename,
            'module_slug' => $request->input('module_slug'),
        ]);

        return response()->json(['id' => $diagram->id, 'message' => 'Diagram saved.']);
    }

    /**
     * Update an existing diagram's XML file.
     */
    public function update(Request $request, Diagram $diagram)
    {
        abort_if(!Auth::user()->isAdmin() || $diagram->user_id !== Auth::id(), 403);

        if ($request->has('xml_data') && $request->input('xml_data')) {
            // Ensure the file exists and update it
            $filename = $diagram->file_path ?: ('diagrams/' . Str::uuid() . '.drawio');
            Storage::disk('local')->put($filename, $request->input('xml_data'));
            $diagram->file_path = $filename;
        }

        $diagram->fill($request->only('title', 'module_slug', 'is_published'));
        $diagram->xml_data = null; // clear old blob data on save
        $diagram->save();

        return response()->json(['status' => 'updated', 'updated_at' => $diagram->updated_at->diffForHumans()]);
    }

    /**
     * Serve the raw .drawio file content for download or editor loading.
     */
    public function file(Diagram $diagram)
    {
        abort_if(!Auth::user()->isAdmin() && !$diagram->is_published, 403);

        $path = $diagram->file_path;
        if (!$path || !Storage::disk('local')->exists($path)) {
            // fallback to old xml_data
            if ($diagram->xml_data) {
                return response($diagram->xml_data, 200)->header('Content-Type', 'application/xml');
            }
            abort(404, 'Diagram file not found.');
        }

        $content = Storage::disk('local')->get($path);
        return response($content, 200)
            ->header('Content-Type', 'application/xml')
            ->header('Content-Disposition', 'inline; filename="' . basename($path) . '"');
    }

    /**
     * Toggle published state.
     */
    public function publish(Diagram $diagram)
    {
        abort_if(!Auth::user()->isAdmin() || $diagram->user_id !== Auth::id(), 403);
        $diagram->update(['is_published' => !$diagram->is_published]);
        $label = $diagram->is_published ? 'published' : 'unpublished';
        return response()->json(['status' => $label]);
    }

    /**
     * Delete a diagram and its file.
     */
    public function destroy(Diagram $diagram)
    {
        abort_if(!Auth::user()->isAdmin() || $diagram->user_id !== Auth::id(), 403);
        if ($diagram->file_path && Storage::disk('local')->exists($diagram->file_path)) {
            Storage::disk('local')->delete($diagram->file_path);
        }
        $diagram->delete();
        return redirect()->route('diagrams.index')->with('success', 'Diagram deleted.');
    }

    /**
     * Show a diagram (viewer).
     */
    public function show(Diagram $diagram)
    {
        abort_if(!$diagram->is_published && $diagram->user_id !== Auth::id(), 404);
        return view('diagrams.show', compact('diagram'));
    }
}
