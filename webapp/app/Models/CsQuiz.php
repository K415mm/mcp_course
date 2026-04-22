<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CsQuiz extends Model
{
    protected $fillable = [
        'cs_session_id',
        'type',
        'question',
        'prompt',
        'options',
        'correct_answers',
        'base_points',
        'is_open',
        'results',
        'phase_index',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'json',
            'correct_answers' => 'json',
            'results' => 'json',
            'is_open' => 'boolean',
            'base_points' => 'integer',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(CsSession::class, 'cs_session_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(CsQuizEntry::class);
    }

    public function teamHasAnswered(CsTeam $team): bool
    {
        return $this->entries()->where('cs_team_id', $team->id)->exists();
    }
}
