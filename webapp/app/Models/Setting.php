<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type'];

    /**
     * Get the decoded value based on its type.
     */
    public function getValueAttribute($value)
    {
        if ($this->type === 'boolean') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }
        
        if ($this->type === 'json' || $this->type === 'array') {
            return json_decode($value, true);
        }

        if ($this->type === 'integer') {
            return (int) $value;
        }

        return $value;
    }
}
