<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CsDecision extends Model
{
    protected $fillable = [
        'cs_session_id', 'cs_team_id', 'cs_player_id',
        'type', 'content', 'phase_index', 'score_awarded',
    ];

    protected function casts(): array
    {
        return ['score_awarded' => 'integer'];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(CsSession::class, 'cs_session_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(CsTeam::class, 'cs_team_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(CsPlayer::class, 'cs_player_id');
    }
}
