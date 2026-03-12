<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\QuizService;
use App\Services\CourseService;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function __construct(
        protected QuizService $quizService,
        protected CourseService $courseService
    ) {
    }

    public function edit(string $moduleSlug, string $lessonSlug)
    {
        $quiz = $this->quizService->getQuizForLesson($moduleSlug, $lessonSlug);
        $items = $this->courseService->getAllItems();
        return view('admin.quiz.edit', compact('quiz', 'moduleSlug', 'lessonSlug', 'items'));
    }

    public function update(Request $request, string $moduleSlug, string $lessonSlug)
    {
        $questions = $request->input('questions', []);
        // Filter out empty questions
        $questions = array_values(array_filter($questions, fn($q) => !empty($q['q'])));
        $this->quizService->saveQuiz($moduleSlug, $lessonSlug, $questions);
        return redirect()->route('admin.quiz.edit', [$moduleSlug, $lessonSlug])
            ->with('success', 'Quiz saved with ' . count($questions) . ' questions.');
    }
}
