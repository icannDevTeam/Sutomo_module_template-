<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}

