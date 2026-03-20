<?php

namespace App\Http\Controllers;

use App\Services\CourseService;
use App\Services\MarkdownService;
use App\Services\QuizService;
use App\Models\Diagram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    public function __construct(
        protected CourseService $courseService,
        protected MarkdownService $markdownService,
        protected QuizService $quizService
    ) {
    }

    /** All modules & workshops listing — marks locked ones */
    public function index()
    {
        $items = $this->courseService->getAllItems();
        $user = Auth::user();

        // Annotate each item with access info
        foreach ($items as &$item) {
            $item['locked'] = !$user->canAccessModule($item['number'], $item['type']);
        }
        unset($item);

        return view('course.index', ['items' => $items]);
    }

    /** Module detail view — enforces access */
    public function module(string $moduleSlug)
    {
        $module = $this->courseService->getItem($moduleSlug);
        abort_if(!$module, 404, 'Module not found.');

        // Access control
        $user = Auth::user();
        if (!$user->canAccessModule($module['number'], $module['type'])) {
            return view('course.locked', [
                'module' => $module,
                'userRole' => $user->roleLabel(),
                'allItems' => $this->courseService->getAllItems(),
            ]);
        }

        $lessons = $this->courseService->getLessons($moduleSlug);
        $overviewFile = $this->courseService->getOverviewFile($moduleSlug);

        $parsed = $overviewFile ? $this->markdownService->parseFile($overviewFile) : null;
        $overviewHtml = $parsed ? $parsed['html'] : '<p class="text-muted">No overview available.</p>';

        return view('course.module', [
            'module' => $module,
            'lessons' => $lessons,
            'overviewHtml' => $overviewHtml,
            'allItems' => $this->courseService->getAllItems(),
        ]);
    }

    /** Individual lesson page — marks lesson as seen */
    public function lesson(string $moduleSlug, string $section, string $lessonSlug)
    {
        $module = $this->courseService->getItem($moduleSlug);
        abort_if(!$module, 404, 'Module not found.');

        $user = Auth::user();
        abort_if(!$user->canAccessModule($module['number'], $module['type']), 403, 'You do not have access to this module.');

        // ── Virtual section: diagrams ─────────────────────────────────
        if ($section === 'diagrams') {
            // lessonSlug is 'diagram-{id}'
            $diagramId = str_replace('diagram-', '', $lessonSlug);
            $diagram   = Diagram::findOrFail($diagramId);
            abort_if(!$diagram->is_published && $diagram->user_id !== $user->id, 404);

            $lessons = $this->courseService->getLessons($moduleSlug);
            return view('course.lesson', [
                'module'       => $module,
                'section'      => 'diagrams',
                'lessonSlug'   => $lessonSlug,
                'lessonTitle'  => $diagram->title,
                'contentType'  => 'diagram',
                'meta'         => [],
                'contentHtml'  => '',
                'quiz'         => null,
                'lessons'      => $lessons,
                'allItems'     => $this->courseService->getAllItems(),
                'prevLesson'   => null,
                'nextLesson'   => null,
                'diagram'      => $diagram,
            ]);
        }

        // ── Regular file-based lesson ─────────────────────────────────
        $filePath = $this->courseService->getLessonFile($moduleSlug, $section, $lessonSlug);
        abort_if(!$filePath, 404, 'Lesson not found.');

        $status = $this->courseService->getFileStatus($filePath);
        if ($status !== 'published' && !$user->isAdmin()) {
            abort(404, 'Lesson not found.');
        }

        $parsed = $this->markdownService->parseFile($filePath);
        $lessons = $this->courseService->getLessons($moduleSlug);

        $this->recordProgress($moduleSlug, $section, $lessonSlug);

        $quiz = null;
        if ($parsed['type'] === 'quiz') {
            $quiz = $this->quizService->getQuizForLesson($moduleSlug, $lessonSlug);
        }

        $flatLessons = [];
        foreach ($lessons as $sec => $data) {
            foreach ($data['lessons'] as $lesson) {
                $flatLessons[] = $lesson;
            }
        }
        $currentIdx = -1;
        foreach ($flatLessons as $idx => $l) {
            if ($l['slug'] === $lessonSlug && $l['section'] === $section) {
                $currentIdx = $idx;
                break;
            }
        }

        return view('course.lesson', [
            'module'       => $module,
            'section'      => $section,
            'lessonSlug'   => $lessonSlug,
            'lessonTitle'  => $parsed['meta']['title'] ?? basename($filePath, '.md'),
            'contentType'  => $parsed['type'],
            'meta'         => $parsed['meta'],
            'contentHtml'  => $parsed['html'],
            'quiz'         => $quiz,
            'lessons'      => $lessons,
            'allItems'     => $this->courseService->getAllItems(),
            'prevLesson'   => $currentIdx > 0 ? $flatLessons[$currentIdx - 1] : null,
            'nextLesson'   => $currentIdx < count($flatLessons) - 1 ? $flatLessons[$currentIdx + 1] : null,
        ]);
    }

    /** AJAX/POST: manually mark a lesson as seen. */
    public function markProgress(Request $request)
    {
        $data = $request->validate([
            'module' => 'required|string',
            'section' => 'required|string',
            'lesson' => 'required|string',
        ]);
        $this->recordProgress($data['module'], $data['section'], $data['lesson']);
        return response()->json(['ok' => true]);
    }

    /** AJAX/POST: grade a quiz attempt */
    public function gradeQuiz(Request $request)
    {
        $data = $request->validate([
            'module_slug' => 'required|string',
            'lesson_slug' => 'required|string',
            'answers' => 'required|array'
        ]);

        $quiz = $this->quizService->getQuizForLesson($data['module_slug'], $data['lesson_slug']);
        if (!$quiz) {
            return response()->json(['error' => 'Quiz not found'], 404);
        }

        $results = $this->quizService->gradeAttempt($quiz, $data['answers']);
        return response()->json($results);
    }

    private function recordProgress(string $module, string $section, string $lesson): void
    {
        $user = Auth::user();
        if (!$user)
            return;

        $key = "{$module}.{$section}.{$lesson}";
        $progress = $user->modules_viewed ?? [];
        if (!isset($progress[$key])) {
            $progress[$key] = now()->toDateTimeString();
            $user->update(['modules_viewed' => $progress]);
        }
    }
}

