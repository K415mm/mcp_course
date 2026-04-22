<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CsEntity extends Model
{
    protected $table = 'cs_entities';

    protected $fillable = [
        'type',
        'name',
        'role_label',
        'color',
        'icon',
        'logo_path',
        'sort_order',
        'is_scored',
        'can_vote',
        'badge_eligible',
        'show_in_ranking',
        'role_mode',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_scored' => 'boolean',
            'can_vote' => 'boolean',
            'badge_eligible' => 'boolean',
            'show_in_ranking' => 'boolean',
        ];
    }
}
