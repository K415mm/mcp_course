<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameCardPlay extends Model
{
    protected $fillable = [
        'game_round_id', 'game_team_id', 'game_card_id',
        'user_id', 'target_system', 'result', 'points_earned', 'played_at',
    ];

    protected function casts(): array
    {
        return [
            'result'        => 'json',
            'points_earned' => 'integer',
            'played_at'     => 'datetime',
        ];
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(GameRound::class, 'game_round_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(GameTeam::class, 'game_team_id');
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(GameCard::class, 'game_card_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
