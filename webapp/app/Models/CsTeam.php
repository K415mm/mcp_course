<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CsTeam extends Model
{
    protected $fillable = [
        'cs_session_id', 'type', 'name', 'role_label', 'color', 'icon', 'score',
    ];

    protected function casts(): array
    {
        return ['score' => 'integer'];
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
        $score = $this->score;
        foreach (array_reverse(self::badges()) as $b) {
            if ($score >= $b['min']) return $b;
        }
        return self::badges()[0];
    }

    public static function badges(): array
    {
        return [
            ['min' => 0,  'icon' => '🥉', 'name' => 'OBSERVATEUR'],
            ['min' => 26, 'icon' => '🥈', 'name' => 'ANALYSTE'],
            ['min' => 51, 'icon' => '🥇', 'name' => 'STRATÉGISTE'],
            ['min' => 76, 'icon' => '💎', 'name' => 'CYBER HÉROS'],
        ];
    }

    public static function defaultTeams(): array
    {
        return [
            ['type' => 'ancs',      'name' => 'ANCS',      'role_label' => 'Commandement national',  'color' => '#00b4d8', 'icon' => '🏛️'],
            ['type' => 'cert',      'name' => 'CERT',      'role_label' => 'Détection technique',    'color' => '#2dc653', 'icon' => '🔍'],
            ['type' => 'finance',   'name' => 'FINANCE',   'role_label' => 'Secteur bancaire',       'color' => '#f4a261', 'icon' => '🏦'],
            ['type' => 'transport', 'name' => 'TRANSPORT', 'role_label' => 'Mobilité critique',      'color' => '#8b5cf6', 'icon' => '🚆'],
            ['type' => 'egov',      'name' => 'E-GOV',     'role_label' => 'Services citoyens',      'color' => '#fbbf24', 'icon' => '🖥️'],
            ['type' => 'comm',      'name' => 'COMM',      'role_label' => 'Communication de crise', 'color' => '#e63946', 'icon' => '📡'],
        ];
    }
}
