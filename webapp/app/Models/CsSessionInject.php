<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CsSessionInject extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'cs_session_id', 'cs_inject_id', 'phase_index', 'triggered_by', 'triggered_at',
    ];

    protected function casts(): array
    {
        return ['triggered_at' => 'datetime'];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(CsSession::class, 'cs_session_id');
    }

    public function inject(): BelongsTo
    {
        return $this->belongsTo(CsInject::class, 'cs_inject_id');
    }
}
