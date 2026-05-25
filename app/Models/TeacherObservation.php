<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherObservation extends Model
{
    protected $guarded = [];

    protected $casts = [
        'observed_at'     => 'datetime',
        'follow_up_date'  => 'date',
        'dimensions'      => 'array',
    ];

    public const DIMENSIONS = [
        'engagement'      => 'Engagement',
        'clarity'         => 'Clarity',
        'pacing'          => 'Pacing',
        'classroom_mgmt'  => 'Classroom Management',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function observer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'observer_id');
    }

    public function getAverageScoreAttribute(): ?float
    {
        $d = $this->dimensions ?? [];
        $vals = array_filter(array_map(fn ($v) => is_numeric($v) ? (float) $v : null, $d), fn ($v) => $v !== null);
        return count($vals) ? round(array_sum($vals) / count($vals), 1) : null;
    }
}
