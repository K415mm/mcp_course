<?php

namespace App\Services;

class CsContentBankService
{
    public function getPhaseContent(string $scenarioKey, int $phaseIndex): array
    {
        $filePath = resource_path("cs_content/{$scenarioKey}/phase_{$phaseIndex}.json");

        if (!is_file($filePath)) {
            return [
                'messages' => [],
                'questions' => [],
            ];
        }

        $raw = file_get_contents($filePath);
        if ($raw === false || trim($raw) === '') {
            return [
                'messages' => [],
                'questions' => [],
            ];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [
                'messages' => [],
                'questions' => [],
            ];
        }

        return [
            'messages' => is_array($decoded['messages'] ?? null) ? $decoded['messages'] : [],
            'questions' => is_array($decoded['questions'] ?? null) ? $decoded['questions'] : [],
        ];
    }
}
