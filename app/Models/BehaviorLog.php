<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BehaviorLog extends Model
{
    protected $guarded = [];

    protected $casts = ['occurred_at' => 'date'];

    public const CATEGORIES = [
        'incident'   => 'Incident',
        'warning'    => 'Warning',
        'counseling' => 'Counseling',
        'positive'   => 'Positive',
    ];

    public const SEVERITIES = [
        'low' => 'Low', 'medium' => 'Medium',
        'high' => 'High', 'critical' => 'Critical',
    ];

    public const SEVERITY_COLORS = [
        'low' => 'gray', 'medium' => 'warning',
        'high' => 'danger', 'critical' => 'danger',
    ];

    public const STATUSES = [
        'open'             => 'Open',
        'unit_review'      => 'Unit Head Review',
        'vp_review'        => 'VP Review',
        'principal_action' => 'Principal Action',
        'closed'           => 'Closed',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
