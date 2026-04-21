<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CsEntity extends Model
{
    protected $table = 'cs_entities';

    protected $fillable = [
        'type', 'name', 'role_label', 'color', 'icon', 'logo_path'
    ];
}
