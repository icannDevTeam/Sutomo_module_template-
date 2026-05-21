<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimetableLesson extends Model
{
    protected $guarded = [];

    protected $casts = [
        'locked'    => 'bool',
        'conflicts' => 'array',
    ];

    public function definition(): BelongsTo
    {
        return $this->belongsTo(TimetableDefinition::class, 'definition_id');
    }

    public function slotKey(): string
    {
        return "{$this->session}-{$this->period}-{$this->day}";
    }
}
