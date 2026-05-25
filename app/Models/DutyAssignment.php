<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DutyAssignment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'starts_at'    => 'datetime',
        'ends_at'      => 'datetime',
        'responded_at' => 'datetime',
    ];

    public const STATUSES = [
        'pending'   => 'Pending',
        'accepted'  => 'Accepted',
        'declined'  => 'Declined',
        'completed' => 'Completed',
    ];

    public const STATUS_COLORS = [
        'pending' => 'warning', 'accepted' => 'info',
        'declined' => 'danger', 'completed' => 'success',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
