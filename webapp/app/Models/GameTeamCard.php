<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameTeamCard extends Model
{
    protected $fillable = [
        'game_team_id', 'game_card_id', 'status',
        'acquired_round', 'remaining_turns',
    ];

    protected function casts(): array
    {
        return [
            'acquired_round'  => 'integer',
            'remaining_turns' => 'integer',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(GameTeam::class, 'game_team_id');
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(GameCard::class, 'game_card_id');
    }

    // ── Status helpers ──────────────────────────────────────────────

    public function isInHand(): bool   { return $this->status === 'hand'; }
    public function isActive(): bool   { return $this->status === 'active'; }
    public function isUsed(): bool     { return $this->status === 'used'; }

    public function markActive(?int $duration = null): void
    {
        $this->update([
            'status'          => 'active',
            'remaining_turns' => $duration,
        ]);
    }

    public function markUsed(): void
    {
        $this->update(['status' => 'used']);
    }

    /** Tick down duration-based active cards; mark used if expired */
    public function tickDuration(): bool
    {
        if ($this->status !== 'active' || $this->remaining_turns === null) {
            return false;
        }
        $this->remaining_turns--;
        if ($this->remaining_turns <= 0) {
            $this->markUsed();
            return true; // expired
        }
        $this->save();
        return false;
    }
}
