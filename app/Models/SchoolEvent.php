<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolEvent extends Model
{
    protected $guarded = [];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at'   => 'date',
    ];

    public const CATEGORIES = [
        'competition' => 'Competition',
        'fieldtrip'   => 'Field Trip',
        'graduation'  => 'Graduation',
        'pta'         => 'PTA',
        'cca'         => 'CCA / ECA',
    ];

    public const STATUSES = [
        'draft'       => 'Draft',
        'pending'     => 'Pending Approval',
        'approved'    => 'Approved',
        'in_progress' => 'In Progress',
        'completed'   => 'Completed',
    ];

    public const STATUS_COLORS = [
        'draft' => 'gray', 'pending' => 'warning',
        'approved' => 'success', 'in_progress' => 'info', 'completed' => 'gray',
    ];
}
