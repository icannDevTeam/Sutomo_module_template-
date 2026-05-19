<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $guarded = [];

    protected $casts = [
        'dob'                  => 'date',
        'enrolled_at'          => 'date',
        'books_issued_at'      => 'date',
        'account_activated_at' => 'date',
        'first_attendance_at'  => 'date',
        'meta'                 => 'array',
        'gpa'                  => 'decimal:2',
    ];

    public const ONBOARDING_STEPS = [
        'accepted'      => 'Accepted',
        'temp_id'       => 'Temporary ID',
        'dev_fee'       => 'Development Fee',
        'books'         => 'Books Issued',
        'class_assigned'=> 'Class Assigned',
        'attendance'    => '5-Day Attendance',
        'permanent_id'  => 'Permanent ID + VA',
        'activated'     => 'Activated',
    ];

    public const STATUSES = [
        'active'      => 'Active',
        'observation' => 'Observation',
        'retained'    => 'Retained',
        'exited'      => 'Exited',
        'expelled'    => 'Expelled',
    ];

    public const STATUS_COLORS = [
        'active' => 'success', 'observation' => 'warning',
        'retained' => 'info', 'exited' => 'gray', 'expelled' => 'danger',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function behaviorLogs(): HasMany
    {
        return $this->hasMany(BehaviorLog::class);
    }

    public function sscRequests(): HasMany
    {
        return $this->hasMany(SscRequest::class);
    }
}
