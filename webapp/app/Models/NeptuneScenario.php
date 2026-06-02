<?php

namespace App\Models;

class NeptuneScenario
{
    public static function all(): array
    {
        return [
            'neptune_strike' => self::neptuneStrike(),
        ];
    }

    public static function find(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    public static function list(): array
    {
        return collect(self::all())->map(fn($s) => [
            'key'         => $s['key'],
            'title'       => $s['title'],
            'description' => $s['description'],
            'difficulty'  => $s['difficulty'],
            'duration'    => $s['duration_minutes'] . ' min',
        ])->values()->all();
    }

    private static function neptuneStrike(): array
    {
        return [
            'key'              => 'neptune_strike',
            'title'            => 'Neptune Strike',
            'description'      => 'Immersive maritime cyber crisis simulation for the Mediterranean Forum on Maritime Security.',
            'difficulty'       => 'Expert',
            'duration_minutes' => 45,
            'attacker_name'    => 'APT-POSEIDON',
            'attacker_icon'    => '⚓',
            'language'         => 'en',
            'teams' => [
                ['type' => 'political', 'name' => 'Political Cell',      'role_label' => 'Ministry / SGDSN',          'color' => '#ff3355', 'icon' => '🏛️', 'is_scored' => true,  'can_vote' => true,  'badge_eligible' => true,  'show_in_ranking' => true,  'role_mode' => 'participant'],
                ['type' => 'naval',     'name' => 'Naval Command',       'role_label' => 'Marine Nationale / NATO',   'color' => '#00aaff', 'icon' => '⚓', 'is_scored' => true,  'can_vote' => true,  'badge_eligible' => true,  'show_in_ranking' => true,  'role_mode' => 'participant'],
                ['type' => 'cert',      'name' => 'Maritime CERT',       'role_label' => 'ANSSI / Cyber Command',     'color' => '#00ffcc', 'icon' => '🛡️', 'is_scored' => true,  'can_vote' => true,  'badge_eligible' => true,  'show_in_ranking' => true,  'role_mode' => 'participant'],
                ['type' => 'port',      'name' => 'Port Authority',      'role_label' => 'Grand Port Maritime',       'color' => '#ffaa00', 'icon' => '🚢', 'is_scored' => true,  'can_vote' => true,  'badge_eligible' => true,  'show_in_ranking' => true,  'role_mode' => 'participant'],
                ['type' => 'eunatocc',  'name' => 'EU/NATO Coord',       'role_label' => 'EUNAVFOR / ENISA',          'color' => '#aa88ff', 'icon' => '🌐', 'is_scored' => true,  'can_vote' => true,  'badge_eligible' => true,  'show_in_ranking' => true,  'role_mode' => 'participant'],
            ],
            'phantom_messages' => [
                'APT-POSEIDON has breached the maritime corridor.',
                'The shipping lines are vulnerable. Secure your systems.',
                'A collective response is your only option.',
            ],
            'phases' => [
                ['index' => 0, 'name' => 'PHASE 1', 'tag' => 'DETECTION',   'desc' => 'Initial Detection',        'duration_seconds' => 540],
                ['index' => 1, 'name' => 'PHASE 2', 'tag' => 'ANALYSIS',    'desc' => 'Threat Analysis',           'duration_seconds' => 540],
                ['index' => 2, 'name' => 'PHASE 3', 'tag' => 'ESCALATION',  'desc' => 'Escalation',                'duration_seconds' => 540],
                ['index' => 3, 'name' => 'PHASE 4', 'tag' => 'RESPONSE',    'desc' => 'Strategic Response',        'duration_seconds' => 540],
                ['index' => 4, 'name' => 'PHASE 5', 'tag' => 'DEBRIEF',     'desc' => 'Debrief',                   'duration_seconds' => 540],
            ],
            'vote_options' => [
                ['key' => 'A', 'label' => 'Option A', 'color' => '#ff3355'],
                ['key' => 'B', 'label' => 'Option B', 'color' => '#00aaff'],
                ['key' => 'C', 'label' => 'Option C', 'color' => '#00ffcc'],
                ['key' => 'D', 'label' => 'Option D', 'color' => '#ffaa00'],
            ],
        ];
    }
}
