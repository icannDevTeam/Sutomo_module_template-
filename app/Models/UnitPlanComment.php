<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitPlanComment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'resolved' => 'boolean',
    ];

    public function unitPlan(): BelongsTo
    {
        return $this->belongsTo(UnitPlan::class);
    }
}
