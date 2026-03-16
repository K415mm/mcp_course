<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitation extends Model
{
    protected $fillable = ['email', 'name', 'token', 'invited_by', 'accepted_at', 'expires_at'];

    protected $casts = [
        'accepted_at' => 'datetime',
        'expires_at'  => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────
    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    // ── Helper methods ─────────────────────────────────────────────
    public function isAccepted(): bool
    {
        return !is_null($this->accepted_at);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return !$this->isAccepted() && !$this->isExpired();
    }

    // ── Scopes ─────────────────────────────────────────────────────
    public function scopePending($query)
    {
        return $query->whereNull('accepted_at')
                     ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function scopeAccepted($query)
    {
        return $query->whereNotNull('accepted_at');
    }
}
