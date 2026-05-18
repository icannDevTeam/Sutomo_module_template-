<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $guarded = [];

    protected $casts = [
        'dob'             => 'date',
        'joined_at'       => 'date',
        'contract_end'    => 'date',
        'last_review'     => 'date',
        'certifications'  => 'array',
        'languages'       => 'array',
        'rating'          => 'float',
    ];

    public const STATUSES = [
        'permanent' => 'Permanent',
        'contract'  => 'Contract',
        'probation' => 'Probation',
        'opl'       => 'OPL',
        'leave'     => 'On Leave',
        'alumni'    => 'Alumni',
    ];
}
