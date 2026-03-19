<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Diagram extends Model
{
    protected $fillable = ['user_id', 'title', 'xml_data', 'file_path', 'module_slug', 'is_published'];

    protected $casts = ['is_published' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get the URL to serve the raw .drawio file (used to load editor or download).
     */
    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_path && !$this->xml_data) {
            return null;
        }
        return route('diagrams.file', $this->id);
    }

    /**
     * Check if this diagram has actual content (file or legacy blob).
     */
    public function hasContent(): bool
    {
        if ($this->file_path && Storage::disk('local')->exists($this->file_path)) {
            return true;
        }
        return (bool) $this->xml_data;
    }
}
