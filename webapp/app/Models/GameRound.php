<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameRound extends Model
{
    protected $fillable = [
        'game_session_id', 'round_number', 'phase',
        'started_at', 'ended_at', 'event_card',
    ];

    protected function casts(): array
    {
        return [
            'round_number' => 'integer',
            'phase'        => 'integer',
            'started_at'   => 'datetime',
            'ended_at'     => 'datetime',
            'event_card'   => 'json',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(GameSession::class, 'game_session_id');
    }

    public function cardPlays(): HasMany
    {
        return $this->hasMany(GameCardPlay::class);
    }

    public function isActive(): bool
    {
        return $this->started_at !== null && $this->ended_at === null;
    }
}
