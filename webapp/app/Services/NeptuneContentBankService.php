<?php

namespace App\Services;

class NeptuneContentBankService
{
    public function getPhaseContent(string $scenarioKey, int $phaseIndex): array
    {
        $filePath = resource_path("neptune_content/{$scenarioKey}/phase_{$phaseIndex}.json");

        if (!is_file($filePath)) {
            return [
                'messages' => [],
                'questions' => [],
                'media' => [],
            ];
        }

        $raw = file_get_contents($filePath);
        if ($raw === false || trim($raw) === '') {
            return [
                'messages' => [],
                'questions' => [],
                'media' => [],
            ];
        }

        // Handle UTF-8 BOM
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [
                'messages' => [],
                'questions' => [],
                'media' => [],
            ];
        }

        $messages = is_array($decoded['messages'] ?? null) ? $decoded['messages'] : [];
        $questions = is_array($decoded['questions'] ?? null) ? $decoded['questions'] : [];
        $media = is_array($decoded['media'] ?? null) ? $decoded['media'] : [];

        return [
            'messages' => array_values(array_map(fn(array $m) => $this->normalizeMessage($m), array_filter($messages, 'is_array'))),
            'questions' => array_values(array_map(fn(array $q) => $this->normalizeQuestion($q), array_filter($questions, 'is_array'))),
            'media' => array_values(array_map(fn(array $m) => $this->normalizeMedia($m), array_filter($media, 'is_array'))),
        ];
    }

    private function normalizeMessage(array $message): array
    {
        return [
            'type' => (string) ($message['type'] ?? 'info'),
            'content' => (string) ($message['content'] ?? ''),
            'stage' => $message['stage'] ?? null,
            'tag' => $message['tag'] ?? null,
            'related_media' => is_array($message['related_media'] ?? null) ? array_values($message['related_media']) : [],
        ];
    }

    private function normalizeQuestion(array $question): array
    {
        return [
            'id' => (string) ($question['id'] ?? ''),
            'type' => (string) ($question['type'] ?? 'single_choice'),
            'question' => (string) ($question['question'] ?? ''),
            'prompt' => (string) ($question['prompt'] ?? ''),
            'secret' => (bool) ($question['secret'] ?? false),
            'time_limit' => isset($question['time_limit']) ? (int) $question['time_limit'] : null,
            'points' => isset($question['points']) ? (int) $question['points'] : null,
            'options' => is_array($question['options'] ?? null) ? array_values($question['options']) : [],
            'acceptable_answers' => is_array($question['acceptable_answers'] ?? null) ? array_values($question['acceptable_answers']) : [],
            'correct_order' => is_array($question['correct_order'] ?? null) ? array_values($question['correct_order']) : [],
            'related_media' => is_array($question['related_media'] ?? null) ? array_values($question['related_media']) : [],
            'note' => (string) ($question['note'] ?? ''),
        ];
    }

    private function normalizeMedia(array $media): array
    {
        return [
            'id' => (string) ($media['id'] ?? ''),
            'type' => (string) ($media['type'] ?? 'image'),
            'title' => (string) ($media['title'] ?? ''),
            'caption' => (string) ($media['caption'] ?? ''),
            'url' => (string) ($media['url'] ?? ''),
            'thumbnail' => (string) ($media['thumbnail'] ?? ''),
            'autoplay' => (bool) ($media['autoplay'] ?? false),
            'loop' => (bool) ($media['loop'] ?? false),
            'muted' => (bool) ($media['muted'] ?? true),
            'stage' => $media['stage'] ?? null,
        ];
    }
}
