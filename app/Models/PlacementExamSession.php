<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlacementExamSession extends Model
{
    protected $guarded = [];

    protected $casts = [
        'starts_at' => 'datetime',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(EnrollmentPeriod::class, 'enrollment_period_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'supervisor_teacher_id');
    }
}
