<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    /**
     * List all notes for the authenticated user.
     */
    public function index()
    {
        $notes = Note::where('user_id', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->get();

        $activeNote = $notes->first();

        return view('notes.index', compact('notes', 'activeNote'));
    }

    /**
     * Show a single note for editing.
     */
    public function show(Note $note)
    {
        abort_if($note->user_id !== Auth::id(), 403);
        $notes = Note::where('user_id', Auth::id())->orderBy('updated_at', 'desc')->get();
        return view('notes.index', compact('notes', 'note'))->with('activeNote', $note);
    }

    /**
     * Create a new blank note.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'nullable|string|max:255',
            'module_slug' => 'nullable|string|max:255',
        ]);

        $note = Note::create([
            'user_id'     => Auth::id(),
            'title'       => $request->input('title', 'Untitled Note'),
            'module_slug' => $request->input('module_slug'),
            'body'        => '',
            'body_markdown' => '',
        ]);

        return response()->json(['id' => $note->id, 'title' => $note->title]);
    }

    /**
     * Auto-save note content (called by TipTap on change).
     */
    public function update(Request $request, Note $note)
    {
        abort_if($note->user_id !== Auth::id(), 403);

        $request->validate([
            'title'         => 'nullable|string|max:255',
            'body'          => 'nullable|string',
            'body_markdown' => 'nullable|string',
        ]);

        $note->update($request->only('title', 'body', 'body_markdown'));

        return response()->json(['status' => 'saved', 'updated_at' => $note->updated_at->diffForHumans()]);
    }

    /**
     * Delete a note.
     */
    public function destroy(Note $note)
    {
        abort_if($note->user_id !== Auth::id(), 403);
        $note->delete();

        return response()->json(['status' => 'deleted']);
    }

    /**
     * Return notes for a specific module (used by the lesson drawer).
     */
    public function forModule(string $moduleSlug)
    {
        $notes = Note::where('user_id', Auth::id())
            ->where('module_slug', $moduleSlug)
            ->orderBy('updated_at', 'desc')
            ->get(['id', 'title', 'updated_at']);

        return response()->json($notes);
    }
}
