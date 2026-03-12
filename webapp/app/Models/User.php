<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    // ── Access-level constants ──────────────────────────────────────
    const ROLE_GUEST = 'guest';      // Module 1 only
    const ROLE_PREENROL = 'preenrol';   // Modules 1-2
    const ROLE_STUDENT = 'student';    // Modules 1-4
    const ROLE_CSTUDENT = 'cstudent';   // Everything
    const ROLE_ADMIN = 'admin';      // Everything + admin panel

    const ROLES = [
        self::ROLE_GUEST,
        self::ROLE_PREENROL,
        self::ROLE_STUDENT,
        self::ROLE_CSTUDENT,
        self::ROLE_ADMIN,
    ];

    /**
     * Module access map: role => max module numbers accessible.
     * Workshops follow the same tier as modules.
     */
    const MODULE_ACCESS = [
        self::ROLE_GUEST => [1],
        self::ROLE_PREENROL => [1, 2],
        self::ROLE_STUDENT => [1, 2, 3, 4],
        self::ROLE_CSTUDENT => '*',   // all
        self::ROLE_ADMIN => '*',   // all
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'bio',
        'job_title',
        'avatar',
        'modules_viewed',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'modules_viewed' => 'array',
        ];
    }

    // ── Role helpers ────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Can this user access a given module/workshop number?
     */
    public function canAccessModule(int $moduleNumber, string $type = 'module'): bool
    {
        $access = self::MODULE_ACCESS[$this->role] ?? [];

        // cstudent and admin get everything
        if ($access === '*') {
            return true;
        }

        // Workshops: same access tier as modules
        return in_array($moduleNumber, $access);
    }

    /**
     * Numeric access tier for comparisons (0-4).
     */
    public function accessLevel(): int
    {
        return match ($this->role) {
            self::ROLE_GUEST => 0,
            self::ROLE_PREENROL => 1,
            self::ROLE_STUDENT => 2,
            self::ROLE_CSTUDENT => 3,
            self::ROLE_ADMIN => 4,
            default => 0,
        };
    }

    /**
     * Human-readable role label.
     */
    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_GUEST => 'Guest',
            self::ROLE_PREENROL => 'Pre-Enrolled',
            self::ROLE_STUDENT => 'Student',
            self::ROLE_CSTUDENT => 'Certified Student',
            self::ROLE_ADMIN => 'Admin',
            default => ucfirst($this->role),
        };
    }

    /**
     * Role badge CSS class for UI.
     */
    public function roleBadgeClass(): string
    {
        return match ($this->role) {
            self::ROLE_GUEST => 'bg-secondary',
            self::ROLE_PREENROL => 'bg-info',
            self::ROLE_STUDENT => 'bg-success',
            self::ROLE_CSTUDENT => 'bg-warning text-dark',
            self::ROLE_ADMIN => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    // ── Profile helpers ─────────────────────────────────────────────

    public function avatarUrl(): string
    {
        return $this->avatar
            ? asset('storage/avatars/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=0d6efd&color=fff&size=80';
    }

    public function initials(): string
    {
        $parts = explode(' ', trim($this->name));
        $initials = strtoupper(substr($parts[0], 0, 1));
        if (count($parts) > 1)
            $initials .= strtoupper(substr(end($parts), 0, 1));
        return $initials;
    }
}
