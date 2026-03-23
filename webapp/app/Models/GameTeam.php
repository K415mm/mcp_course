<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameTeam extends Model
{
    protected $fillable = [
        'game_session_id', 'type', 'tokens', 'shop_tokens', 'score',
    ];

    protected function casts(): array
    {
        return [
            'tokens'      => 'integer',
            'shop_tokens' => 'integer',
            'score'       => 'integer',
        ];
    }

    // ── Constants ───────────────────────────────────────────────────

    const MAX_TOKENS = 20;
    const MAX_CARRY_OVER = 2;
    const BASE_TOKENS_PER_ROUND = 4;
    const SHOP_TOKENS_PER_ROUND = 2;
    const MAX_HAND_SIZE = 10;
    const STARTING_HAND_SIZE = 8;

    // ── Relationships ───────────────────────────────────────────────

    public function session(): BelongsTo
    {
        return $this->belongsTo(GameSession::class, 'game_session_id');
    }

    public function players(): HasMany
    {
        return $this->hasMany(GamePlayer::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(GameTeamCard::class);
    }

    public function cardPlays(): HasMany
    {
        return $this->hasMany(GameCardPlay::class);
    }

    // ── Helpers ─────────────────────────────────────────────────────

    public function isBlue(): bool { return $this->type === 'blue'; }
    public function isRed(): bool  { return $this->type === 'red'; }

    public function label(): string
    {
        return $this->isBlue() ? 'Blue Team' : 'Red Team';
    }

    public function colorClass(): string
    {
        return $this->isBlue() ? 'primary' : 'danger';
    }

    public function hand()
    {
        return $this->cards()->where('status', 'hand')->with('card');
    }

    public function activeCards()
    {
        return $this->cards()->where('status', 'active')->with('card');
    }

    public function handCount(): int
    {
        return $this->cards()->where('status', 'hand')->count();
    }

    public function canAfford(int $cost): bool
    {
        return $this->tokens >= $cost;
    }

    public function spendTokens(int $amount): bool
    {
        if ($this->tokens < $amount) return false;
        $this->decrement('tokens', $amount);
        return true;
    }

    public function addTokens(int $amount): void
    {
        $this->tokens = min(self::MAX_TOKENS, $this->tokens + $amount);
        $this->save();
    }

    public function addShopTokens(int $amount): void
    {
        $this->shop_tokens += $amount;
        $this->save();
    }

    public function spendShopTokens(int $amount): bool
    {
        if ($this->shop_tokens < $amount) return false;
        $this->decrement('shop_tokens', $amount);
        return true;
    }

    public function addScore(int $points): void
    {
        $this->increment('score', $points);
    }

    /** Distribute base resources at start of round */
    public function distributeRoundResources(): void
    {
        $this->addTokens(self::BASE_TOKENS_PER_ROUND);
        $this->addShopTokens(self::SHOP_TOKENS_PER_ROUND);
    }
}
