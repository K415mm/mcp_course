<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamePlayer extends Model
{
    protected $fillable = [
        'game_session_id', 'game_team_id', 'user_id', 'is_captain',
    ];

    protected function casts(): array
    {
        return ['is_captain' => 'boolean'];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(GameSession::class, 'game_session_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(GameTeam::class, 'game_team_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
