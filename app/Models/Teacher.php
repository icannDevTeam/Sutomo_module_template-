<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Teacher extends Model
{
    protected $guarded = [];

    protected $casts = [
        'dob'             => 'date',
        'joined_at'       => 'date',
        'contract_end'    => 'date',
        'last_review'     => 'date',
        'certifications'  => 'array',
        'languages'       => 'array',
        'awards'          => 'array',
        'initiatives'     => 'array',
        'rating'          => 'float',
    ];

    public const STATUSES = [
        'permanent' => 'Permanent',
        'contract'  => 'Contract',
        'probation' => 'Probation',
        'opl'       => 'OPL',
        'leave'     => 'On Leave',
        'alumni'    => 'Alumni',
    ];

    public const TITLES = [
        'teacher'             => 'Teacher',
        'senior_teacher'      => 'Senior Teacher',
        'subject_coordinator' => 'Subject Coordinator',
        'unit_head'           => 'Unit Head',
        'vice_principal'      => 'Vice Principal',
        'principal'           => 'Principal',
    ];

    public const TITLE_COLORS = [
        'teacher'             => 'gray',
        'senior_teacher'      => 'info',
        'subject_coordinator' => 'warning',
        'unit_head'           => 'success',
        'vice_principal'      => 'danger',
        'principal'           => 'danger',
    ];

    public function leaves(): HasMany
    {
        return $this->hasMany(TeacherLeave::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TeacherDocument::class);
    }

    public function voluntaryRequests(): HasMany
    {
        return $this->hasMany(VoluntaryRequest::class);
    }

    public function duties(): HasMany
    {
        return $this->hasMany(DutyAssignment::class);
    }

    public function trainings(): HasMany
    {
        return $this->hasMany(TeacherTraining::class);
    }

    public function childrenStudents(): HasMany
    {
        return $this->hasMany(Student::class, 'parent_teacher_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(TeacherTag::class);
    }

    public function homeroomClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'homeroom_teacher_id');
    }

    /** Avatar URL accessor — falls back to a tiny initials data-uri-free placeholder path. */
    protected function avatarUrl(): Attribute
    {
        return Attribute::get(function () {
            return $this->avatar_path
                ? Storage::disk('public')->url($this->avatar_path)
                : null;
        });
    }

    /** Normalize phone to E.164 (+62…) on save. Accepts 08… / 628… / +628… / spaces / dashes. */
    protected function phone(): Attribute
    {
        return Attribute::set(fn ($value) => self::normalizePhone($value));
    }

    protected function emergencyContactPhone(): Attribute
    {
        return Attribute::set(fn ($value) => self::normalizePhone($value));
    }

    public static function normalizePhone(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        // Strip everything except digits and a leading +
        $clean = preg_replace('/[^\d+]/', '', $value);
        if ($clean === '' || $clean === '+') {
            return null;
        }
        if (str_starts_with($clean, '+')) {
            return $clean;
        }
        if (str_starts_with($clean, '0')) {
            return '+62' . substr($clean, 1);
        }
        if (str_starts_with($clean, '62')) {
            return '+' . $clean;
        }
        return '+62' . $clean;
    }
}


