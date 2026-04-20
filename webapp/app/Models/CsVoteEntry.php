<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CsVoteEntry extends Model
{
    public $timestamps = false;

    protected $fillable = ['cs_vote_id', 'cs_team_id', 'choice', 'voted_at'];

    protected function casts(): array
    {
        return ['voted_at' => 'datetime'];
    }

    public function vote(): BelongsTo
    {
        return $this->belongsTo(CsVote::class, 'cs_vote_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(CsTeam::class, 'cs_team_id');
    }
}
