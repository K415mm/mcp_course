<?php

namespace App\Services;

use App\Models\Diagram;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

/**
 * CourseService — Multi-Course File-Based Scanner
 *
 * Folder hierarchy:
 *   {COURSE_CONTENT_PATH}/
 *     {Course_Name}/                  ← Course
 *       course.md                     ← Course front-matter (title, status, ...)
 *       {Module_NN_Name}/             ← Module / Workshop
 *         module.md (optional)        ← Module front-matter
 *         theoretical/                ← Element Category
 *           01_Lesson.md              ← Lesson
 *         practical/
 *         examples/
 *         slides_prompt/
 *         [diagrams is virtual — from DB]
 */
class CourseService
{
    protected string $rootPath;
    protected array  $elementCategories;

    public function __construct()
    {
        $this->rootPath          = config('course.content_path');
        $this->elementCategories = config('course.sections', []);
    }

    // ──────────────────────────────────────────────────────────────────
    // COURSES
    // ──────────────────────────────────────────────────────────────────

    /**
     * List all courses found in the root content directory.
     */
    public function getCourses(): array
    {
        if (!File::isDirectory($this->rootPath)) {
            return [];
        }

        $courses = [];
        foreach (File::directories($this->rootPath) as $dir) {
            $course = $this->parseCourseDirectory($dir);
            if ($course) {
                $courses[] = $course;
            }
        }

        // Sort by order (from course.md) or alphabetically
        usort($courses, fn($a, $b) => ($a['order'] ?? 999) <=> ($b['order'] ?? 999));
        return $courses;
    }

    /**
     * Get a single course by slug.
     */
    public function getCourse(string $courseSlug): ?array
    {
        foreach ($this->getCourses() as $c) {
            if ($c['slug'] === $courseSlug) return $c;
        }
        return null;
    }

    // ──────────────────────────────────────────────────────────────────
    // MODULES (within a course)
    // ──────────────────────────────────────────────────────────────────

    /**
     * Get all modules/workshops for a course.
     */
    public function getModules(string $courseSlug): array
    {
        $course = $this->getCourse($courseSlug);
        if (!$course) return [];

        $modules = [];
        foreach (File::directories($course['path']) as $dir) {
            $module = $this->parseModuleDirectory($dir, $courseSlug);
            if ($module) {
                $modules[] = $module;
            }
        }

        usort($modules, fn($a, $b) => $a['order'] <=> $b['order']);
        return $modules;
    }

    /**
     * Get a single module by slug within a course.
     */
    public function getModule(string $courseSlug, string $moduleSlug): ?array
    {
        foreach ($this->getModules($courseSlug) as $m) {
            if ($m['slug'] === $moduleSlug) return $m;
        }
        return null;
    }

    // ─── Legacy compatibility: getAllItems() maps to the first course's modules ──
    public function getAllItems(): array
    {
        $courses = $this->getCourses();
        if (empty($courses)) return [];
        // For legacy backwards-compat, return modules of the first published course
        $course = collect($courses)->firstWhere('status', 'published') ?? $courses[0];
        return $this->getModules($course['slug']);
    }

    public function getItem(string $slug): ?array
    {
        $courses = $this->getCourses();
        foreach ($courses as $course) {
            foreach ($this->getModules($course['slug']) as $m) {
                if ($m['slug'] === $slug) return $m;
            }
        }
        return null;
    }

    // ──────────────────────────────────────────────────────────────────
    // ELEMENT CATEGORIES & LESSONS
    // ──────────────────────────────────────────────────────────────────

    /**
     * Get all element categories for a module, including the virtual 'diagrams' category.
     * Returns: [ 'theoretical' => ['label' => '...', 'lessons' => [...]], 'diagrams' => [...] ]
     */
    public function getLessons(string $moduleSlug, bool $includeUnpublished = false): array
    {
        $module = $this->getItem($moduleSlug);
        if (!$module) return [];

        $user    = Auth::user();
        $showAll = $includeUnpublished || ($user && $user->isAdmin());

        $lessons = [];

        foreach ($this->elementCategories as $categoryKey => $categoryLabel) {

            // ── Virtual: diagrams category ─────────────────────────────
            if ($categoryKey === 'diagrams') {
                $diagrams = $this->getDiagramsForModule($moduleSlug, $module['course_slug'] ?? null);
                if ($diagrams->isNotEmpty()) {
                    $diagramLessons = $diagrams->map(fn($d) => [
                        'slug'    => 'diagram-' . $d->id,
                        'title'   => $d->title,
                        'path'    => null,
                        'section' => 'diagrams',
                        'status'  => $d->is_published ? 'published' : 'draft',
                        'type'    => 'diagram',
                        'diagram' => $d,
                    ])->values()->all();

                    $lessons['diagrams'] = [
                        'label'   => 'Diagrams',
                        'lessons' => $diagramLessons,
                    ];
                }
                continue;
            }

            // ── Regular file-based category ────────────────────────────
            $sectionPath = $module['path'] . DIRECTORY_SEPARATOR . $categoryKey;
            if (!File::isDirectory($sectionPath)) continue;

            $sectionLessons = [];
            foreach (File::files($sectionPath) as $file) {
                if (strtolower($file->getExtension()) !== 'md') continue;

                $status = $this->getFileStatus($file->getRealPath());
                if (!$showAll && $status !== 'published') continue;

                $sectionLessons[] = [
                    'slug'    => Str::slug($file->getFilenameWithoutExtension()),
                    'title'   => $this->prettifyFilename($file->getFilenameWithoutExtension()),
                    'path'    => $file->getRealPath(),
                    'section' => $categoryKey,
                    'status'  => $status,
                    'type'    => 'lesson',
                ];
            }

            if (!empty($sectionLessons)) {
                $lessons[$categoryKey] = [
                    'label'   => $categoryLabel,
                    'lessons' => $sectionLessons,
                ];
            }
        }

        return $lessons;
    }

    /**
     * Resolve a specific lesson file path.
     */
    public function getLessonFile(string $moduleSlug, string $section, string $lessonSlug): ?string
    {
        $module = $this->getItem($moduleSlug);
        if (!$module) return null;

        $sectionPath = $module['path'] . DIRECTORY_SEPARATOR . $section;
        if (!File::isDirectory($sectionPath)) return null;

        foreach (File::files($sectionPath) as $file) {
            if (strtolower($file->getExtension()) !== 'md') continue;
            if (Str::slug($file->getFilenameWithoutExtension()) === $lessonSlug) {
                return $file->getRealPath();
            }
        }
        return null;
    }

    /**
     * Get overview .md for a module.
     */
    public function getOverviewFile(string $moduleSlug): ?string
    {
        $module = $this->getItem($moduleSlug);
        if (!$module) return null;

        $rootMds = collect(File::files($module['path']))
            ->filter(fn($f) => strtolower($f->getExtension()) === 'md')
            ->values();

        if ($rootMds->isEmpty()) return null;

        $preferred = $rootMds->first(fn($f) => preg_match('/(Module|Workshop)_\\d+_Content\\.md$/i', $f->getFilename()));
        return ($preferred ?? $rootMds->first())->getRealPath();
    }

    // ──────────────────────────────────────────────────────────────────
    // FILE STATUS HELPERS
    // ──────────────────────────────────────────────────────────────────

    public function getFileStatus(string $absolutePath): string
    {
        if (!File::exists($absolutePath)) return 'draft';

        $content = File::get($absolutePath);
        if (str_starts_with(ltrim($content), '---')) {
            $trimmed = ltrim($content);
            $end     = strpos($trimmed, '---', 3);
            if ($end !== false) {
                $yaml = substr($trimmed, 3, $end - 3);
                foreach (explode("\n", $yaml) as $line) {
                    $line = trim($line);
                    if (preg_match('/^status\s*:\s*(.+)$/i', $line, $m)) {
                        return strtolower(trim($m[1], "\"' "));
                    }
                }
            }
        }
        return 'draft';
    }

    public function setFileStatus(string $absolutePath, string $status): bool
    {
        if (!File::exists($absolutePath)) return false;

        $content = File::get($absolutePath);
        $status  = strtolower($status);

        if (str_starts_with(ltrim($content), '---')) {
            $trimmed = ltrim($content);
            $end     = strpos($trimmed, '---', 3);
            if ($end !== false) {
                $yaml = substr($trimmed, 3, $end - 3);
                $body = substr($trimmed, $end + 3);
                if (preg_match('/^status\s*:.+$/mi', $yaml)) {
                    $yaml = preg_replace('/^status\s*:.+$/mi', "status: {$status}", $yaml);
                } else {
                    $yaml = rtrim($yaml) . "\nstatus: {$status}\n";
                }
                File::put($absolutePath, "---{$yaml}---{$body}");
                return true;
            }
        }

        File::put($absolutePath, "---\nstatus: {$status}\n---\n\n{$content}");
        return true;
    }

    // ──────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ──────────────────────────────────────────────────────────────────

    private function parseCourseDirectory(string $dir): ?array
    {
        $courseFile = $dir . DIRECTORY_SEPARATOR . 'course.md';
        $meta       = File::exists($courseFile) ? $this->parseFrontMatter(File::get($courseFile)) : [];

        // A course folder must have at least one module subfolder or course.md
        $hasModules = !empty(collect(File::directories($dir))
            ->filter(fn($d) => preg_match('/^(Module|Workshop)_/', basename($d)))->all());

        if (!$hasModules && !File::exists($courseFile)) return null;

        $name = basename($dir);
        $slug = Str::slug($name);

        return [
            'slug'        => $slug,
            'name'        => $name,
            'path'        => $dir,
            'title'       => $meta['title'] ?? str_replace('_', ' ', $name),
            'description' => $meta['description'] ?? '',
            'status'      => $meta['status'] ?? 'draft',
            'thumbnail'   => $meta['thumbnail'] ?? null,
            'author'      => $meta['author'] ?? null,
            'order'       => (int) ($meta['order'] ?? 999),
        ];
    }

    private function parseModuleDirectory(string $dir, string $courseSlug): ?array
    {
        $name = basename($dir);

        if (!preg_match('/^(Module|Workshop)_(\d+)_(.+)$/', $name, $m)) return null;

        $type  = strtolower($m[1]);
        $order = (int) $m[2];
        $title = str_replace('_', ' ', $m[3]);

        // Check optional module.md
        $moduleFile = $dir . DIRECTORY_SEPARATOR . 'module.md';
        $meta       = File::exists($moduleFile) ? $this->parseFrontMatter(File::get($moduleFile)) : [];

        return [
            'type'        => $type,
            'order'       => ($type === 'workshop' ? 100 : 0) + $order,
            'number'      => $order,
            'title'       => $meta['title'] ?? $title,
            'slug'        => Str::slug($name),
            'name'        => $name,
            'folder'      => $name,
            'path'        => $dir,
            'course_slug' => $courseSlug,
            'icon'        => $type === 'workshop' ? 'bi-tools' : 'bi-book',
            'color'       => $type === 'workshop' ? '#f59e0b' : '#3b82f6',
            'status'      => $meta['status'] ?? 'published', // modules default published
        ];
    }

    private function getDiagramsForModule(string $moduleSlug, ?string $courseSlug)
    {
        $user = Auth::user();

        return Diagram::where('module_slug', $moduleSlug)
            ->where(function ($q) use ($user) {
                $q->where('is_published', true);
                if ($user && $user->isAdmin()) {
                    $q->orWhere('user_id', $user->id);
                }
            })
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    private function parseFrontMatter(string $content): array
    {
        $content = ltrim($content);
        if (!str_starts_with($content, '---')) return [];

        $end = strpos($content, '---', 3);
        if ($end === false) return [];

        $yaml = substr($content, 3, $end - 3);
        $meta = [];

        foreach (explode("\n", $yaml) as $line) {
            $line = trim($line);
            if (preg_match('/^(\w+)\s*:\s*(.+)$/', $line, $m)) {
                $meta[trim($m[1])] = trim($m[2], "\"' ");
            }
        }

        return $meta;
    }

    private function prettifyFilename(string $name): string
    {
        $name = preg_replace('/^\d+[_-]?/', '', $name);
        $name = str_replace(['_', '-'], ' ', $name);
        return ucwords(strtolower($name));
    }
}
