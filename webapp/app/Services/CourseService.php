<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class CourseService
{
    protected string $contentPath;
    protected array $sections;

    public function __construct()
    {
        $this->contentPath = config('course.content_path');
        $this->sections = config('course.sections');
    }

    /**
     * Scan the content directory and return all modules and workshops.
     */
    public function getAllItems(): array
    {
        if (!File::isDirectory($this->contentPath)) {
            return [];
        }

        $dirs = File::directories($this->contentPath);
        $items = [];

        foreach ($dirs as $dir) {
            $item = $this->parseDirectory($dir);
            if ($item) {
                $items[] = $item;
            }
        }

        usort($items, fn($a, $b) => $a['order'] <=> $b['order']);

        return $items;
    }

    /**
     * Load a single module/workshop by slug.
     */
    public function getItem(string $slug): ?array
    {
        $items = $this->getAllItems();
        foreach ($items as $item) {
            if ($item['slug'] === $slug) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Load lessons for a module, grouped by section.
     * Filters out draft files for non-admin users.
     */
    public function getLessons(string $moduleSlug, bool $includeUnpublished = false): array
    {
        $item = $this->getItem($moduleSlug);
        if (!$item)
            return [];

        // Admin users see everything
        $user = Auth::user();
        $showAll = $includeUnpublished || ($user && $user->isAdmin());

        $lessons = [];
        foreach ($this->sections as $sectionKey => $sectionLabel) {
            $sectionPath = $item['path'] . DIRECTORY_SEPARATOR . $sectionKey;
            if (!File::isDirectory($sectionPath))
                continue;

            $files = File::files($sectionPath);
            $sectionLessons = [];
            foreach ($files as $file) {
                if (strtolower($file->getExtension()) !== 'md')
                    continue;

                // Check file status
                $status = $this->getFileStatus($file->getRealPath());

                // Skip draft files for non-admin users
                if (!$showAll && $status !== 'published') {
                    continue;
                }

                $sectionLessons[] = [
                    'slug' => Str::slug($file->getFilenameWithoutExtension()),
                    'title' => $this->prettifyFilename($file->getFilenameWithoutExtension()),
                    'path' => $file->getRealPath(),
                    'section' => $sectionKey,
                    'status' => $status,
                ];
            }
            if (!empty($sectionLessons)) {
                $lessons[$sectionKey] = [
                    'label' => $sectionLabel,
                    'lessons' => $sectionLessons,
                ];
            }
        }

        // Also include root-level .md files (like Module_XX_Content.md)
        $rootMds = collect(File::files($item['path']))
            ->filter(fn($f) => strtolower($f->getExtension()) === 'md')
            ->values();

        if ($rootMds->isNotEmpty()) {
            // Prefer the canonical Module_XX_Content.md file if it exists
            $overview = $rootMds->first(fn($f) => preg_match('/Module_\d+_Content\.md$/i', $f->getFilename()))
                ?? $rootMds->first();
            $item['overview_path'] = $overview->getRealPath();
        }

        return $lessons;
    }

    /**
     * Resolve a specific lesson file path.
     */
    public function getLessonFile(string $moduleSlug, string $section, string $lessonSlug): ?string
    {
        $item = $this->getItem($moduleSlug);
        if (!$item)
            return null;

        $sectionPath = $item['path'] . DIRECTORY_SEPARATOR . $section;
        if (!File::isDirectory($sectionPath))
            return null;

        foreach (File::files($sectionPath) as $file) {
            if (strtolower($file->getExtension()) !== 'md')
                continue;
            if (Str::slug($file->getFilenameWithoutExtension()) === $lessonSlug) {
                return $file->getRealPath();
            }
        }
        return null;
    }

    /**
     * Get the overview .md file for a module.
     */
    public function getOverviewFile(string $moduleSlug): ?string
    {
        $item = $this->getItem($moduleSlug);
        if (!$item)
            return null;

        $rootMds = collect(File::files($item['path']))
            ->filter(fn($f) => strtolower($f->getExtension()) === 'md')
            ->values();

        if ($rootMds->isEmpty()) return null;

        // Prefer the canonical Module_XX_Content.md
        $preferred = $rootMds->first(fn($f) => preg_match('/Module_\d+_Content\.md$/i', $f->getFilename()));
        return ($preferred ?? $rootMds->first())->getRealPath();
    }

    /**
     * Read a .md file's status from front-matter.
     * Returns 'draft' or 'published'.
     */
    public function getFileStatus(string $absolutePath): string
    {
        if (!File::exists($absolutePath)) {
            return 'draft';
        }

        $content = File::get($absolutePath);

        // Check for YAML front-matter
        if (str_starts_with(ltrim($content), '---')) {
            $trimmed = ltrim($content);
            $end = strpos($trimmed, '---', 3);
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

        return 'draft';  // default to draft if no status found
    }

    /**
     * Set the status of a .md file in its front-matter.
     */
    public function setFileStatus(string $absolutePath, string $status): bool
    {
        if (!File::exists($absolutePath)) {
            return false;
        }

        $content = File::get($absolutePath);
        $status = strtolower($status);

        if (str_starts_with(ltrim($content), '---')) {
            $trimmed = ltrim($content);
            $end = strpos($trimmed, '---', 3);
            if ($end !== false) {
                $yaml = substr($trimmed, 3, $end - 3);
                $body = substr($trimmed, $end + 3);

                // Check if status line already exists
                if (preg_match('/^status\s*:.+$/mi', $yaml)) {
                    $yaml = preg_replace('/^status\s*:.+$/mi', "status: {$status}", $yaml);
                } else {
                    $yaml = rtrim($yaml) . "\nstatus: {$status}\n";
                }

                $newContent = "---{$yaml}---{$body}";
                File::put($absolutePath, $newContent);
                return true;
            }
        }

        // No front-matter exists — add it
        $newContent = "---\nstatus: {$status}\n---\n\n{$content}";
        File::put($absolutePath, $newContent);
        return true;
    }

    // -----------------------------------------------------------------
    // Private helpers
    // -----------------------------------------------------------------

    private function parseDirectory(string $dir): ?array
    {
        $name = basename($dir);

        // Match Module_01_Something or Workshop_01_Something
        if (preg_match('/^(Module|Workshop)_(\d+)_(.+)$/', $name, $m)) {
            $type = strtolower($m[1]);
            $order = (int) $m[2];
            $title = str_replace('_', ' ', $m[3]);
            $slug = Str::slug($name);

            return [
                'type' => $type,
                'order' => ($type === 'workshop' ? 100 : 0) + $order,
                'number' => $order,
                'title' => $title,
                'slug' => $slug,
                'name' => $name,
                'folder' => $name,
                'path' => $dir,
                'icon' => $type === 'workshop' ? 'bi-tools' : 'bi-book',
                'color' => $type === 'workshop' ? '#f59e0b' : '#3b82f6',
            ];
        }

        return null;
    }

    private function prettifyFilename(string $name): string
    {
        // Remove leading digits/underscores (like "01_intro" → "Intro")
        $name = preg_replace('/^\d+[_-]?/', '', $name);
        // Replace underscores and hyphens with spaces
        $name = str_replace(['_', '-'], ' ', $name);
        return ucwords(strtolower($name));
    }
}

