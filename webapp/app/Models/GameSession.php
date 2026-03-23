<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class GameSession extends Model
{
    protected $fillable = [
        'name', 'code', 'scenario', 'status',
        'current_round', 'current_phase',
        'moderator_id', 'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'json',
            'scenario' => 'integer',
            'current_round' => 'integer',
            'current_phase' => 'integer',
        ];
    }

    // ── Boot ────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (GameSession $session) {
            if (empty($session->code)) {
                $session->code = strtoupper(Str::random(6));
            }
        });
    }

    // ── Relationships ───────────────────────────────────────────────

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }

    public function teams(): HasMany
    {
        return $this->hasMany(GameTeam::class);
    }

    public function blueTeam(): HasOne
    {
        return $this->hasOne(GameTeam::class)->where('type', 'blue');
    }

    public function redTeam(): HasOne
    {
        return $this->hasOne(GameTeam::class)->where('type', 'red');
    }

    public function players(): HasMany
    {
        return $this->hasMany(GamePlayer::class);
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(GameRound::class);
    }

    // ── Helpers ─────────────────────────────────────────────────────

    public function isLobby(): bool   { return $this->status === 'lobby'; }
    public function isActive(): bool  { return $this->status === 'active'; }
    public function isPaused(): bool  { return $this->status === 'paused'; }
    public function isFinished(): bool { return $this->status === 'finished'; }

    public function currentRound(): ?GameRound
    {
        return $this->rounds()->where('round_number', $this->current_round)->first();
    }

    public function isModerator(User $user): bool
    {
        return $this->moderator_id === $user->id;
    }

    public function playerFor(User $user): ?GamePlayer
    {
        return $this->players()->where('user_id', $user->id)->first();
    }

    public function maxRounds(): int
    {
        return $this->settings['max_rounds'] ?? 8;
    }

    public function timerSeconds(): int
    {
        return $this->settings['timer_seconds'] ?? 900; // 15 min
    }

    /** Scenario title from index */
    public function scenarioTitle(): string
    {
        return match ($this->scenario) {
            1 => 'Opération NightOwl',
            2 => 'Supply Chain Poisoning',
            3 => 'Insider Threat',
            4 => 'Zero-Day API',
            default => 'Custom',
        };
    }

    public function scenarioDifficulty(): string
    {
        return match ($this->scenario) {
            1 => 'Intermédiaire',
            2 => 'Avancé',
            3 => 'Expert',
            4 => 'Avancé',
            default => '—',
        };
    }

    // ── Phase labels ────────────────────────────────────────────────

    public static function phaseLabel(int $phase): string
    {
        return match ($phase) {
            1 => 'Distribution des ressources',
            2 => 'Red Team joue',
            3 => 'Blue Team joue',
            4 => 'Tirage événement',
            5 => 'Scoring & bilan',
            default => 'En attente',
        };
    }

    // ── Scopes ──────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['lobby', 'active', 'paused']);
    }
}
