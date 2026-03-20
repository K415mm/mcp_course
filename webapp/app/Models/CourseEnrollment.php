<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseEnrollment extends Model
{
    protected $fillable = ['user_id', 'course_slug', 'enrolled_by', 'enrolled_at'];

    protected $casts = ['enrolled_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function enrolledBy()
    {
        return $this->belongsTo(User::class, 'enrolled_by');
    }
}
