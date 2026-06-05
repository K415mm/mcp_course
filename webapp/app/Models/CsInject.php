<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CsInject extends Model
{
    protected $fillable = [
        'scenario_key', 'tag', 'content', 'color', 'phase_hint',
        'is_surprise', 'sort_order', 'target_team_type',
        'requires_action', 'expected_action_type',
    ];

    protected function casts(): array
    {
        return [
            'is_surprise' => 'boolean',
            'requires_action' => 'boolean',
        ];
    }

    public function scopeForScenario($q, string $key)
    {
        return $q->where('scenario_key', $key)->orderBy('sort_order');
    }

    public function scopeSurprises($q)
    {
        return $q->where('is_surprise', true);
    }

    public function scopeStandard($q)
    {
        return $q->where('is_surprise', false);
    }

    /**
     * Filter injects visible to a given team type.
     * Returns injects with no target OR matching the given team type.
     */
    public function scopeVisibleToTeam($q, ?string $teamType)
    {
        return $q->where(function ($inner) use ($teamType) {
            $inner->whereNull('target_team_type');
            if ($teamType) {
                $inner->orWhere('target_team_type', $teamType);
            }
        });
    }
}

