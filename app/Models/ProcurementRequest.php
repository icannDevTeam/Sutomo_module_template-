<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementRequest extends Model
{
    protected $guarded = [];

    protected $casts = ['needed_by' => 'date'];

    public const CATEGORIES = [
        'books'     => 'Books',
        'uniforms'  => 'Uniforms',
        'equipment' => 'Equipment',
        'classroom' => 'Classroom',
        'event'     => 'Event',
    ];

    public const STATUSES = [
        'pending'          => 'Pending',
        'principal_review' => 'Principal Review',
        'yayasan_review'   => 'Yayasan Review',
        'approved'         => 'Approved',
        'rejected'         => 'Rejected',
    ];

    public const STATUS_COLORS = [
        'pending' => 'gray', 'principal_review' => 'warning',
        'yayasan_review' => 'info', 'approved' => 'success', 'rejected' => 'danger',
    ];
}
