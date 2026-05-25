<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'campus',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Panel access roles.
     * Admin role is granted everywhere by default for backward compatibility
     * during the role-system rollout. Tighten further once spatie/permissions
     * lands (see /memories/session/teacher-enrichment-plan.md Phase 5).
     */
    public const PRINCIPAL_ROLES = ['principal', 'vice_principal', 'unit_head', 'hr', 'admin', 'superadmin'];
    public const ADMIN_ROLES     = ['admin', 'superadmin'];

    public function canAccessPanel(Panel $panel): bool
    {
        $role = $this->role ?? 'teacher';

        return match ($panel->getId()) {
            'principal' => in_array($role, self::PRINCIPAL_ROLES, true),
            'admin'     => in_array($role, self::ADMIN_ROLES, true),
            default     => false,
        };
    }
}
