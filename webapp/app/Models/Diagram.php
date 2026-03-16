<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diagram extends Model
{
    protected $fillable = ['user_id', 'title', 'xml_data', 'module_slug', 'is_published'];

    protected $casts = ['is_published' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
