<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CsBroadcast extends Model
{
    protected $fillable = [
        'cs_session_id', 'moderator_id', 'message', 'type', 'phase_index', 'is_phantom',
    ];

    protected function casts(): array
    {
        return ['is_phantom' => 'boolean'];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(CsSession::class, 'cs_session_id');
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }
}
