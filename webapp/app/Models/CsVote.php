<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CsVote extends Model
{
    protected $fillable = [
        'cs_session_id', 'question', 'options', 'is_open', 'is_secret', 'results', 'phase_index',
    ];

    protected function casts(): array
    {
        return [
            'options'  => 'json',
            'results'  => 'json',
            'is_open'  => 'boolean',
            'is_secret' => 'boolean',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(CsSession::class, 'cs_session_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(CsVoteEntry::class);
    }

    public function teamHasVoted(CsTeam $team): bool
    {
        return $this->entries()->where('cs_team_id', $team->id)->exists();
    }

    public function tally(): array
    {
        $counts = [];
        foreach ($this->options as $opt) {
            $counts[$opt['key']] = $this->entries()->where('choice', $opt['key'])->count();
        }
        return $counts;
    }
}
