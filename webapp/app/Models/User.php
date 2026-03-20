<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    // ── Role constants ──────────────────────────────────────────────
    const ROLE_GUEST    = 'guest';
    const ROLE_PREENROL = 'preenrol';
    const ROLE_STUDENT  = 'student';
    const ROLE_CSTUDENT = 'cstudent';
    const ROLE_ADMIN    = 'admin';

    const ROLES = [
        self::ROLE_GUEST,
        self::ROLE_PREENROL,
        self::ROLE_STUDENT,
        self::ROLE_CSTUDENT,
        self::ROLE_ADMIN,
    ];

    protected $fillable = [
        'name', 'email', 'password', 'is_admin', 'role',
        'bio', 'job_title', 'avatar', 'modules_viewed',
        'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at',
        'banned_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'          => 'datetime',
            'password'                   => 'hashed',
            'modules_viewed'             => 'array',
            'two_factor_confirmed_at'    => 'datetime',
            'banned_at'                  => 'datetime',
        ];
    }

    // ── Relationships ───────────────────────────────────────────────

    /** Direct course enrollments for this user */
    public function courseEnrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    /** Classes this user belongs to */
    public function classes()
    {
        return $this->belongsToMany(StudentClass::class, 'class_user', 'user_id', 'class_id');
    }

    // ── Access Control ──────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isBanned(): bool
    {
        return !is_null($this->banned_at);
    }

    /**
     * Check if user is enrolled in a given course (direct or via class).
     */
    public function canAccessCourse(string $courseSlug): bool
    {
        if ($this->isAdmin()) return true;

        // Direct enrollment
        if ($this->courseEnrollments()->where('course_slug', $courseSlug)->exists()) {
            return true;
        }

        // Via class enrollment
        $classIds = $this->classes()->pluck('classes.id');
        if ($classIds->isNotEmpty()) {
            return DB::table('class_course_enrollments')
                ->whereIn('class_id', $classIds)
                ->where('course_slug', $courseSlug)
                ->exists();
        }

        return false;
    }

    /**
     * Get all course slugs this user is enrolled in (direct + via class).
     */
    public function enrolledCourseSlugs(): array
    {
        if ($this->isAdmin()) {
            // Admins see all courses — resolved by CourseService
            return ['*'];
        }

        $direct   = $this->courseEnrollments()->pluck('course_slug')->toArray();
        $classIds = $this->classes()->pluck('classes.id');

        $viaClass = [];
        if ($classIds->isNotEmpty()) {
            $viaClass = DB::table('class_course_enrollments')
                ->whereIn('class_id', $classIds)
                ->pluck('course_slug')
                ->toArray();
        }

        return array_unique(array_merge($direct, $viaClass));
    }

    /**
     * Can this user access a given module/workshop number within a course?
     * Admins always get access. Enrolled users get full access to all modules.
     */
    public function canAccessModule(int $moduleNumber, string $type = 'module', ?string $courseSlug = null): bool
    {
        if ($this->isAdmin()) return true;

        // If course slug provided, check enrollment at course level
        if ($courseSlug) {
            return $this->canAccessCourse($courseSlug);
        }

        // Legacy fallback for code that doesn't pass courseSlug yet
        // Check if user has ANY course enrollment → allow access
        $enrolledSlugs = $this->enrolledCourseSlugs();
        return !empty($enrolledSlugs);
    }

    // ── Profile helpers ─────────────────────────────────────────────

    public function accessLevel(): int
    {
        return match ($this->role) {
            self::ROLE_GUEST    => 0,
            self::ROLE_PREENROL => 1,
            self::ROLE_STUDENT  => 2,
            self::ROLE_CSTUDENT => 3,
            self::ROLE_ADMIN    => 4,
            default             => 0,
        };
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_GUEST    => 'Guest',
            self::ROLE_PREENROL => 'Pre-Enrolled',
            self::ROLE_STUDENT  => 'Student',
            self::ROLE_CSTUDENT => 'Certified Student',
            self::ROLE_ADMIN    => 'Admin',
            default             => ucfirst($this->role),
        };
    }

    public function roleBadgeClass(): string
    {
        return match ($this->role) {
            self::ROLE_GUEST    => 'bg-secondary',
            self::ROLE_PREENROL => 'bg-info',
            self::ROLE_STUDENT  => 'bg-success',
            self::ROLE_CSTUDENT => 'bg-warning text-dark',
            self::ROLE_ADMIN    => 'bg-danger',
            default             => 'bg-secondary',
        };
    }

    public function avatarUrl(): string
    {
        return $this->avatar
            ? asset('storage/avatars/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=0d6efd&color=fff&size=80';
    }

    public function initials(): string
    {
        $parts    = explode(' ', trim($this->name));
        $initials = strtoupper(substr($parts[0], 0, 1));
        if (count($parts) > 1) $initials .= strtoupper(substr(end($parts), 0, 1));
        return $initials;
    }
}
