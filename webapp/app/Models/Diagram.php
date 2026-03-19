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

    /**
     * Get the URL-safe Base64 encoded payload for Kroki.io rendering.
     */
    public function getKrokiUrlAttribute()
    {
        if (!$this->xml_data) {
            return null;
        }
        
        // Compress using zlib level 9
        $compressed = gzcompress($this->xml_data, 9);
        $base64 = base64_encode($compressed);
        
        // Make Base64 URL and Filename Safe (Kroki requirement)
        $urlSafeBase64 = str_replace(['+', '/', '='], ['-', '_', ''], $base64);
        
        return "https://kroki.io/drawio/svg/" . $urlSafeBase64;
    }
}
