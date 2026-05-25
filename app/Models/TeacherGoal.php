<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherGoal extends Model
{
    protected $guarded = [];

    protected $casts = [
        'target_date'           => 'date',
        'set_during_review_at'  => 'date',
        'progress'              => 'integer',
    ];

    public const STATUSES = [
        'on_track'  => 'On Track',
        'at_risk'   => 'At Risk',
        'done'      => 'Done',
        'cancelled' => 'Cancelled',
    ];

    public const STATUS_COLORS = [
        'on_track' => 'success', 'at_risk' => 'warning', 'done' => 'info', 'cancelled' => 'gray',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
