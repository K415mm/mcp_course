<?php

namespace App\Services;

use App\Models\Quiz;
use Illuminate\Support\Facades\Auth;

class QuizService
{
    public function getQuizForLesson(string $moduleSlug, string $lessonSlug): ?Quiz
    {
        return Quiz::where('module_slug', $moduleSlug)
            ->where('lesson_slug', $lessonSlug)
            ->first();
    }

    public function saveQuiz(string $moduleSlug, string $lessonSlug, array $questions): Quiz
    {
        return Quiz::updateOrCreate(
            ['module_slug' => $moduleSlug, 'lesson_slug' => $lessonSlug],
            ['questions' => $questions, 'updated_by' => Auth::id()]
        );
    }

    /**
     * Grade a quiz attempt.
     * $answers = ['0' => 'B', '1' => 'C', ...]
     * Returns ['score' => 80, 'total' => 5, 'correct' => 4, 'results' => [...]]
     */
    public function gradeAttempt(Quiz $quiz, array $answers): array
    {
        $questions = $quiz->questions;
        $correct = 0;
        $results = [];

        foreach ($questions as $i => $q) {
            $userAnswer = $answers[$i] ?? null;
            $isCorrect = ($userAnswer === $q['answer']);
            if ($isCorrect)
                $correct++;
            $results[] = [
                'question' => $q['q'],
                'user_answer' => $userAnswer,
                'correct' => $q['answer'],
                'is_correct' => $isCorrect,
                'explanation' => $q['explanation'] ?? '',
            ];
        }

        $total = count($questions);
        return [
            'score' => $total > 0 ? (int) round($correct / $total * 100) : 0,
            'total' => $total,
            'correct' => $correct,
            'results' => $results,
        ];
    }
}
