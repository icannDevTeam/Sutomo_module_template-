<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimetableDefinition extends Model
{
    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
    ];

    public function lessons(): HasMany
    {
        return $this->hasMany(TimetableLesson::class, 'definition_id');
    }
}
