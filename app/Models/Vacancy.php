<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vacancy extends Model
{
    protected $guarded = [];

    protected $casts = [
        'posted_at'  => 'date',
        'closes_at'  => 'date',
        'featured'   => 'boolean',
        'openings'   => 'integer',
        'applicants' => 'integer',
    ];

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }
}
