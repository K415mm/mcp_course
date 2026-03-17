<?php

namespace App\Http\Controllers;

use App\Models\Diagram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiagramController extends Controller
{
    /**
     * List diagrams. Admins see all their diagrams. Students see all published diagrams.
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
        return view('diagrams.editor', compact('diagram'));
    }

    /**
     * Save (create or update) a diagram from the draw.io PostMessage.
     */
    public function store(Request $request)
    {
        abort_if(!Auth::user()->isAdmin(), 403);

        $request->validate([
            'title'       => 'nullable|string|max:255',
            'xml_data'    => 'required|string',
            'module_slug' => 'nullable|string|max:255',
        ]);

        $diagram = Diagram::create([
            'user_id'     => Auth::id(),
            'title'       => $request->input('title', 'Untitled Diagram'),
            'xml_data'    => $request->input('xml_data'),
            'module_slug' => $request->input('module_slug'),
        ]);

        return response()->json(['id' => $diagram->id, 'message' => 'Diagram saved.']);
    }

    /**
     * Update an existing diagram's XML data.
     */
    public function update(Request $request, Diagram $diagram)
    {
        abort_if(!Auth::user()->isAdmin() || $diagram->user_id !== Auth::id(), 403);

        $diagram->update($request->only('title', 'xml_data', 'module_slug', 'is_published'));

        return response()->json(['status' => 'updated', 'updated_at' => $diagram->updated_at->diffForHumans()]);
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
     * Delete a diagram.
     */
    public function destroy(Diagram $diagram)
    {
        abort_if(!Auth::user()->isAdmin() || $diagram->user_id !== Auth::id(), 403);
        $diagram->delete();
        return redirect()->route('diagrams.index')->with('success', 'Diagram deleted.');
    }

    /**
     * Public (student) view of a published diagram.
     */
    public function show(Diagram $diagram)
    {
        abort_if(!$diagram->is_published && $diagram->user_id !== Auth::id(), 404);
        return view('diagrams.show', compact('diagram'));
    }
}
