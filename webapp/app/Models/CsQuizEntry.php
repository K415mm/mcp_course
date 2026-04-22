<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CsQuizEntry extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'cs_quiz_id',
        'cs_team_id',
        'answer_key',
        'answer_text',
        'awarded_points',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'answered_at' => 'datetime',
            'awarded_points' => 'integer',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(CsQuiz::class, 'cs_quiz_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(CsTeam::class, 'cs_team_id');
    }
}
