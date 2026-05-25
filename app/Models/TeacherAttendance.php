<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAttendance extends Model
{
    protected $table = 'teacher_attendance';
    protected $guarded = [];

    protected $casts = [
        'date'         => 'date:Y-m-d',
        'check_in_at'  => 'datetime',
        'check_out_at' => 'datetime',
    ];

    public const STATUSES = [
        'present' => 'Present',
        'absent'  => 'Absent',
        'late'    => 'Late',
        'leave'   => 'On Leave',
        'holiday' => 'Holiday',
    ];

    public const STATUS_COLORS = [
        'present' => 'success', 'absent' => 'danger', 'late' => 'warning', 'leave' => 'info', 'holiday' => 'gray',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
