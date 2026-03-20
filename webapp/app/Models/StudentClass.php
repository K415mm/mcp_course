<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StudentClass extends Model
{
    protected $table = 'classes';

    protected $fillable = ['name', 'description', 'year', 'status'];

    /** Students in this class */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_user', 'class_id', 'user_id');
    }

    /** Courses this class is enrolled in */
    public function courses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ClassCourseEnrollment::class, 'class_id');
    }

    /** Course slugs this class is enrolled in */
    public function courseSlugs(): array
    {
        return $this->courses()->pluck('course_slug')->toArray();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
