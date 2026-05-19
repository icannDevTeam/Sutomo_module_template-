<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnrollmentPeriod extends Model
{
    protected $guarded = [];

    protected $casts = [
        'opens_at'  => 'date',
        'closes_at' => 'date',
    ];

    public const STATUSES = [
        'draft'  => 'Draft',
        'open'   => 'Open',
        'closed' => 'Closed',
    ];
}
