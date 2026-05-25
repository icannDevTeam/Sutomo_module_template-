<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherTraining extends Model
{
    protected $guarded = [];

    protected $casts = [
        'starts_on' => 'date',
        'ends_on'   => 'date',
    ];

    public const CATEGORIES = [
        'pedagogy'   => 'Pedagogy',
        'subject'    => 'Subject Mastery',
        'leadership' => 'Leadership',
        'wellness'   => 'Wellness',
        'tech'       => 'Technology',
    ];

    public const STATUSES = [
        'planned'     => 'Planned',
        'in_progress' => 'In Progress',
        'completed'   => 'Completed',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
