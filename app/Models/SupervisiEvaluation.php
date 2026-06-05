<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupervisiEvaluation extends Model
{
    protected $guarded = [];

    protected $casts = ['scheduled_at' => 'date'];

    public const STATUSES = [
        'scheduled' => 'Scheduled',
        'completed' => 'Completed',
        'failed'    => 'Failed',
    ];

    public const STATUS_COLORS = [
        'scheduled' => 'gray', 'completed' => 'success',
        'failed' => 'danger',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
