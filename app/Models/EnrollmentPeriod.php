<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EnrollmentPeriod extends Model
{
    protected $guarded = [];

    protected $casts = [
        'opens_at'        => 'date',
        'closes_at'       => 'date',
        'exam_starts_at'  => 'datetime',
        'pass_threshold'  => 'decimal:2',
        'fail_threshold'  => 'decimal:2',
    ];

    public const STATUSES = [
        'draft'  => 'Draft',
        'open'   => 'Open',
        'closed' => 'Closed',
    ];

    public const STATUS_COLORS = [
        'draft' => 'gray', 'open' => 'success', 'closed' => 'danger',
    ];

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function isAcceptingApplications(): bool
    {
        return $this->status === 'open';
    }
}

