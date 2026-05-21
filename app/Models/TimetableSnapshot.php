<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimetableSnapshot extends Model
{
    protected $guarded = [];

    protected $casts = [
        'payload'      => 'array',
        'published_at' => 'datetime',
    ];

    public function definition(): BelongsTo
    {
        return $this->belongsTo(TimetableDefinition::class, 'definition_id');
    }
}
