<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CsTeam extends Model
{
    protected $fillable = [
        'cs_session_id', 'type', 'name', 'role_label', 'color', 'icon', 'score', 'logo_path',
        'is_scored', 'can_vote', 'badge_eligible', 'show_in_ranking', 'role_mode',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'is_scored' => 'boolean',
            'can_vote' => 'boolean',
            'badge_eligible' => 'boolean',
            'show_in_ranking' => 'boolean',
        ];
    }

    // ── Relationships ────────────────────────────────────────────────

    public function session(): BelongsTo
    {
        return $this->belongsTo(CsSession::class, 'cs_session_id');
    }

    public function players(): HasMany
    {
        return $this->hasMany(CsPlayer::class);
    }

    public function activePlayers(): HasMany
    {
        return $this->hasMany(CsPlayer::class)
            ->where('last_seen_at', '>=', now()->subSeconds(15));
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(CsDecision::class);
    }

    // ── Scoring ──────────────────────────────────────────────────────

    public function addScore(int $delta): void
    {
        $this->increment('score', $delta);
    }

    public function setScore(int $value): void
    {
        $this->update(['score' => max(0, $value)]);
    }

    // ── Helpers ──────────────────────────────────────────────────────

    public function badge(): array
    {
        if (!$this->badge_eligible) {
            return ['min' => 0, 'icon' => '🧭', 'name' => 'MENTOR', 'image' => null];
        }

        $score = $this->score;
        foreach (array_reverse(self::badges()) as $b) {
            if ($score >= $b['min']) return $b;
        }
        return self::badges()[0];
    }

    public function isMentor(): bool
    {
        return $this->role_mode === 'mentor';
    }

    public function isScorable(): bool
    {
        return (bool) $this->is_scored && !$this->isMentor();
    }

    public function canVote(): bool
    {
        return (bool) $this->can_vote && !$this->isMentor();
    }

    public static function badges(): array
    {
        return [
            ['min' =>  0, 'icon' => '🥉', 'name' => 'OBSERVATEUR', 'image' => '/cs-assets/badges/observateur.png'],
            ['min' => 26, 'icon' => '🥈', 'name' => 'ANALYSTE',    'image' => '/cs-assets/badges/analyst.png'],
            ['min' => 51, 'icon' => '🥇', 'name' => 'STRATÈGE',    'image' => '/cs-assets/badges/stratege.png'],
            ['min' => 76, 'icon' => '💎', 'name' => 'CYBER HÉROS', 'image' => '/cs-assets/badges/cyber_hero.png'],
        ];
    }

    public static function defaultTeams(): array
    {
        $entities = \App\Models\CsEntity::all();
        if ($entities->isNotEmpty()) {
            return $entities->map(function($e) {
                return [
                    'type'       => $e->type,
                    'name'       => $e->name,
                    'role_label' => $e->role_label,
                    'color'      => $e->color,
                    'icon'       => $e->icon,
                    'logo_path'  => $e->logo_path,
                    'is_scored' => $e->type !== 'ancs',
                    'can_vote' => $e->type !== 'ancs',
                    'badge_eligible' => $e->type !== 'ancs',
                    'show_in_ranking' => $e->type !== 'ancs',
                    'role_mode' => $e->type === 'ancs' ? 'mentor' : 'participant',
                ];
            })->toArray();
        }

        return [
            ['type' => 'ancs',      'name' => 'ANCS',      'role_label' => 'Commandement national',  'color' => '#00b4d8', 'icon' => '🏛️', 'is_scored' => false, 'can_vote' => false, 'badge_eligible' => false, 'show_in_ranking' => false, 'role_mode' => 'mentor'],
            ['type' => 'cert',      'name' => 'CERT',      'role_label' => 'Détection technique',    'color' => '#2dc653', 'icon' => '🔍', 'is_scored' => true,  'can_vote' => true,  'badge_eligible' => true,  'show_in_ranking' => true,  'role_mode' => 'participant'],
            ['type' => 'finance',   'name' => 'FINANCE',   'role_label' => 'Secteur bancaire',       'color' => '#f4a261', 'icon' => '🏦', 'is_scored' => true,  'can_vote' => true,  'badge_eligible' => true,  'show_in_ranking' => true,  'role_mode' => 'participant'],
            ['type' => 'transport', 'name' => 'TRANSPORT', 'role_label' => 'Mobilité critique',      'color' => '#8b5cf6', 'icon' => '🚆', 'is_scored' => true,  'can_vote' => true,  'badge_eligible' => true,  'show_in_ranking' => true,  'role_mode' => 'participant'],
            ['type' => 'egov',      'name' => 'E-GOV',     'role_label' => 'Services citoyens',      'color' => '#fbbf24', 'icon' => '🖥️', 'is_scored' => true,  'can_vote' => true,  'badge_eligible' => true,  'show_in_ranking' => true,  'role_mode' => 'participant'],
            ['type' => 'comm',      'name' => 'COMM',      'role_label' => 'Communication de crise', 'color' => '#e63946', 'icon' => '📡', 'is_scored' => true,  'can_vote' => true,  'badge_eligible' => true,  'show_in_ranking' => true,  'role_mode' => 'participant'],
        ];
    }
}
