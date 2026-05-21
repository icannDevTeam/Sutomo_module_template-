<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitPlanCollaborator extends Model
{
    protected $guarded = [];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'online_now'   => 'boolean',
    ];

    public function unitPlan(): BelongsTo
    {
        return $this->belongsTo(UnitPlan::class);
    }
}
