<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleCompletion extends Model
{
    protected $fillable = ['user_id', 'module_slug', 'completed_at', 'email_sent_at'];

    protected $casts = [
        'completed_at'  => 'datetime',
        'email_sent_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the completion email has already been sent.
     */
    public function emailSent(): bool
    {
        return !is_null($this->email_sent_at);
    }
}
