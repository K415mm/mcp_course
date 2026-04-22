<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CsSession extends Model
{
    protected $fillable = [
        'name', 'code', 'scenario_key', 'status',
        'moderator_id', 'current_phase_index',
        'timer_ends_at', 'timer_paused_remaining',
        'atmosphere', 'settings', 'started_at', 'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'settings'                => 'json',
            'timer_ends_at'           => 'datetime',
            'started_at'              => 'datetime',
            'ended_at'                => 'datetime',
            'current_phase_index'     => 'integer',
            'timer_paused_remaining'  => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CsSession $s) {
            if (empty($s->code)) {
                $s->code = strtoupper(Str::random(6));
            }
        });
    }

    // ── Relationships ────────────────────────────────────────────────

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }

    public function teams(): HasMany
    {
        return $this->hasMany(CsTeam::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(CsPlayer::class);
    }

    public function broadcasts(): HasMany
    {
        return $this->hasMany(CsBroadcast::class)->orderByDesc('created_at');
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(CsDecision::class)->orderByDesc('created_at');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(CsVote::class);
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(CsQuiz::class);
    }

    public function injectLog(): HasMany
    {
        return $this->hasMany(CsSessionInject::class)->orderByDesc('triggered_at');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    public function isLobby(): bool    { return $this->status === 'lobby'; }
    public function isActive(): bool   { return $this->status === 'active'; }
    public function isPaused(): bool   { return $this->status === 'paused'; }
    public function isFinished(): bool { return $this->status === 'finished'; }

    public function scenario(): array
    {
        return CsScenario::find($this->scenario_key) ?? CsScenario::find('phantom_grid');
    }

    public function phases(): array
    {
        return $this->scenario()['phases'] ?? [];
    }

    public function currentPhase(): ?array
    {
        return $this->phases()[$this->current_phase_index] ?? null;
    }

    /**
     * Compute timer remaining seconds from server-authoritative DB state.
     * Returns null if timer is not running.
     */
    public function timerRemainingSeconds(): ?int
    {
        // Paused — return stored value
        if ($this->isPaused() || $this->timer_ends_at === null) {
            return $this->timer_paused_remaining;
        }

        // Running — compute from end timestamp
        $remaining = (int) now()->diffInSeconds($this->timer_ends_at, false);
        return max(0, $remaining);
    }

    public function timerIsRunning(): bool
    {
        return $this->isActive() && $this->timer_ends_at !== null && $this->timer_ends_at->isFuture();
    }

    public function isModerator(User $user): bool
    {
        return $this->moderator_id === $user->id;
    }

    public function openVote(): ?CsVote
    {
        return $this->votes()->where('is_open', true)->latest()->first();
    }

    public function openQuiz(): ?CsQuiz
    {
        return $this->quizzes()->where('is_open', true)->latest()->first();
    }

    public function teamByType(string $type): ?CsTeam
    {
        return $this->teams->firstWhere('type', $type);
    }
}
