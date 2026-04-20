<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CsInject extends Model
{
    protected $fillable = [
        'scenario_key', 'tag', 'content', 'color', 'phase_hint', 'is_surprise', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['is_surprise' => 'boolean'];
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
}

