<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherLeave extends Model
{
    protected $guarded = [];

    protected $casts = [
        'starts_at'   => 'date',
        'ends_at'     => 'date',
        'decided_at'  => 'datetime',
    ];

    public const TYPES = [
        'sick'       => 'Sick',
        'emergency'  => 'Emergency',
        'sabbatical' => 'Sabbatical',
        'midday'     => 'Mid-day Absence',
        'prior'      => 'Prior Notice',
    ];

    public const STATUSES = [
        'pending'  => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ];

    public const STATUS_COLORS = [
        'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function substitute(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'substitute_teacher_id');
    }
}
