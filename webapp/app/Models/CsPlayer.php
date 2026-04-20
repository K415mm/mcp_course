<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CsPlayer extends Model
{
    protected $fillable = [
        'cs_session_id', 'cs_team_id', 'user_id',
        'display_name', 'is_captain', 'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'is_captain'   => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(CsSession::class, 'cs_session_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(CsTeam::class, 'cs_team_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function heartbeat(): void
    {
        $this->update(['last_seen_at' => now()]);
    }

    public function isOnline(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->diffInSeconds(now()) <= 15;
    }
}
