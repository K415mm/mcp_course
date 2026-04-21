<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CsBadge extends Model
{
    public $timestamps = false;

    protected $table = 'cs_badges';

    protected $fillable = [
        'cs_session_id',
        'cs_team_id',
        'badge_type',
        'badge_label',
        'badge_icon',
        'bonus_points',
        'awarded_by',
        'awarded_at',
    ];

    protected $casts = [
        'awarded_at' => 'datetime',
    ];

    // ── Static catalog ──────────────────────────────────────────────

    public static function catalog(): array
    {
        return [
            'first_responder'     => ['icon' => '📡', 'label' => 'First Responder',     'points' => 5, 'image' => '/cs-assets/badges/observateur.png'],
            'crisis_communicator' => ['icon' => '📢', 'label' => 'Crisis Communicator', 'points' => 5, 'image' => '/cs-assets/badges/analyst.png'],
            'zero_silo'           => ['icon' => '🤝', 'label' => 'Zero Silo',           'points' => 5, 'image' => '/cs-assets/badges/stratege.png'],
            'innovation'          => ['icon' => '💡', 'label' => 'Innovation',           'points' => 5, 'image' => '/cs-assets/badges/cyber_hero.png'],
        ];
    }

    // ── Relationships ───────────────────────────────────────────────

    public function session(): BelongsTo
    {
        return $this->belongsTo(CsSession::class, 'cs_session_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(CsTeam::class, 'cs_team_id');
    }
}
