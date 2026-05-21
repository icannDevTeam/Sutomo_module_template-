<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitPlan extends Model
{
    protected $guarded = [];

    protected $casts = [
        'subjects'         => 'array',
        'sections'         => 'array',
        'starts_on'        => 'date',
        'ends_on'          => 'date',
        'last_activity_at' => 'datetime',
        'completion_pct'   => 'integer',
    ];

    public const STATUSES = [
        'draft'     => 'Draft',
        'active'    => 'In progress',
        'completed' => 'Completed',
        'archived'  => 'Archived',
    ];

    public const STATUS_COLORS = [
        'draft'     => 'gray',
        'active'    => 'indigo',
        'completed' => 'emerald',
        'archived'  => 'slate',
    ];

    public function collaborators(): HasMany
    {
        return $this->hasMany(UnitPlanCollaborator::class)->orderByDesc('online_now')->orderByDesc('last_seen_at');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(UnitPlanComment::class)->latest();
    }
}
