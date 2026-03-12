<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CourseService;
use App\Services\MarkdownService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ContentController extends Controller
{
    public function __construct(
        protected CourseService $courseService,
        protected MarkdownService $markdownService
    ) {
    }

    /** List all .md files across all modules */
    public function index()
    {
        $items = $this->courseService->getAllItems();
        $entries = [];

        foreach ($items as $mod) {
            // Note: pass true to getLessons to include unpublished files
            $lessons = $this->courseService->getLessons($mod['slug'], true);
            foreach ($lessons as $section => $data) {
                foreach ($data['lessons'] as $lesson) {
                    $filePath = $this->courseService->getLessonFile($mod['slug'], $section, $lesson['slug']);
                    $parsed = $filePath ? $this->markdownService->parseFrontMatter(File::get($filePath)) : ['meta' => ['type' => 'lesson']];
                    $entries[] = [
                        'module' => $mod['title'],
                        'module_slug' => $mod['slug'],
                        'section' => $section,
                        'lesson' => $lesson['title'],
                        'lesson_slug' => $lesson['slug'],
                        'type' => $parsed['meta']['type'] ?? 'lesson',
                        'status' => $lesson['status'] ?? 'draft',
                        'file' => $filePath ? basename($filePath) : '—',
                        'path' => $filePath,
                    ];
                }
            }
        }

        return view('admin.content.index', compact('entries', 'items'));
    }

    /** Show editor to create a new .md file */
    public function create(Request $request)
    {
        $moduleSlug = $request->query('module', '');
        $section = $request->query('section', 'theoretical');
        $items = $this->courseService->getAllItems();
        $sections = ['theoretical', 'practical', 'examples', 'slides_prompt'];
        $template = "---\ntype: lesson\nstatus: draft\ntitle: \"New Lesson\"\n---\n\n# New Lesson\n\nStart writing here...\n";
        return view('admin.content.edit', compact('moduleSlug', 'section', 'sections', 'items', 'template'));
    }

    /** Save new .md file */
    public function store(Request $request)
    {
        $data = $request->validate([
            'module_slug' => 'required|string',
            'section' => 'required|string',
            'filename' => 'required|string|regex:/^[\w\-]+$/',
            'content' => 'required|string',
        ]);

        $dir = $this->resolveDir($data['module_slug'], $data['section']);
        $path = $dir . DIRECTORY_SEPARATOR . $data['filename'] . '.md';

        if (File::exists($path)) {
            return back()->withErrors(['filename' => 'A file with this name already exists.']);
        }

        File::ensureDirectoryExists($dir);
        File::put($path, $data['content']);

        return redirect()->route('admin.content.index')->with('success', 'Lesson created: ' . $data['filename'] . '.md');
    }

    /** Show editor for existing .md file */
    public function edit(string $moduleSlug, string $filename)
    {
        $items = $this->courseService->getAllItems();
        $sections = ['theoretical', 'practical', 'examples', 'slides_prompt'];
        $section = 'theoretical';
        $path = null;

        // Find the file across sections
        foreach ($sections as $sec) {
            $try = $this->resolveDir($moduleSlug, $sec) . DIRECTORY_SEPARATOR . $filename . '.md';
            if (File::exists($try)) {
                $path = $try;
                $section = $sec;
                break;
            }
        }

        $template = $path ? File::get($path) : '';
        return view('admin.content.edit', compact('moduleSlug', 'section', 'sections', 'items', 'template', 'filename', 'path'));
    }

    /** Save edited .md file */
    public function update(Request $request, string $moduleSlug, string $filename)
    {
        $data = $request->validate([
            'section' => 'required|string',
            'content' => 'required|string',
        ]);

        $path = $this->resolveDir($moduleSlug, $data['section']) . DIRECTORY_SEPARATOR . $filename . '.md';
        File::put($path, $data['content']);

        return redirect()->route('admin.content.index')->with('success', 'Saved: ' . $filename . '.md');
    }

    /** Toggle status (draft/published) of a .md file */
    public function toggleStatus(Request $request)
    {
        $data = $request->validate([
            'path' => 'required|string',
            'status' => 'required|string|in:draft,published',
        ]);

        $real = realpath($data['path']);
        $base = realpath(config('course.content_path'));

        if (!$real || !str_starts_with($real, $base)) {
            abort(403, 'Invalid path.');
        }

        $this->courseService->setFileStatus($real, $data['status']);
        return redirect()->route('admin.content.index')->with('success', 'File status updated to ' . $data['status'] . '.');
    }

    /** Bulk publish all draft files */
    public function bulkPublish()
    {
        $items = $this->courseService->getAllItems();
        $count = 0;

        foreach ($items as $mod) {
            $lessons = $this->courseService->getLessons($mod['slug'], true);
            foreach ($lessons as $section => $data) {
                foreach ($data['lessons'] as $lesson) {
                    if (($lesson['status'] ?? 'draft') === 'draft') {
                        $this->courseService->setFileStatus($lesson['path'], 'published');
                        $count++;
                    }
                }
            }
        }

        return redirect()->route('admin.content.index')->with('success', "Bulk published {$count} files.");
    }

    /** Delete a .md file */
    public function destroy(Request $request)
    {
        $path = $request->input('path');
        $base = realpath(config('course.content_path'));
        $real = realpath($path);

        // Security: ensure file is within course content directory
        if (!$real || !str_starts_with($real, $base)) {
            abort(403, 'Invalid path.');
        }

        File::delete($path);
        return redirect()->route('admin.content.index')->with('success', 'File deleted.');
    }

    private function resolveDir(string $moduleSlug, string $section): string
    {
        // Find module folder name by slug
        $items = $this->courseService->getAllItems();
        $mod = collect($items)->firstWhere('slug', $moduleSlug);
        if (!$mod)
            abort(404, 'Module not found');

        return config('course.content_path') . DIRECTORY_SEPARATOR . $mod['folder'] . DIRECTORY_SEPARATOR . $section;
    }
}

