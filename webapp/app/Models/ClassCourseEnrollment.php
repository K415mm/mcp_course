<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassCourseEnrollment extends Model
{
    protected $table = 'class_course_enrollments';

    protected $fillable = ['class_id', 'course_slug', 'enrolled_by', 'enrolled_at'];

    protected $casts = ['enrolled_at' => 'datetime'];
}
