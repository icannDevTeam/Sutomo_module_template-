<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherJournalEntry extends Model
{
    protected $guarded = [];

    protected $casts = [
        'entry_date'         => 'date',
        'is_private'         => 'bool',
        'shared_with_mentor' => 'bool',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
