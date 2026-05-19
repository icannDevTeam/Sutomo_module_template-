<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SscRequest extends Model
{
    protected $guarded = [];

    protected $casts = [
        'clearance'    => 'array',
        'requested_at' => 'date',
    ];

    public const TYPES = [
        'visa_letter'    => 'Visa Letter',
        'scholarship'    => 'Scholarship Declaration',
        'behavior_cert'  => 'Behavioral Certificate',
        'exit'           => 'Exit Letter',
    ];

    public const STATUSES = [
        'pending'   => 'Pending',
        'clearance' => 'Clearance In Progress',
        'approved'  => 'Approved',
        'rejected'  => 'Rejected',
        'issued'    => 'Issued',
    ];

    public const STATUS_COLORS = [
        'pending' => 'gray', 'clearance' => 'warning',
        'approved' => 'success', 'rejected' => 'danger', 'issued' => 'info',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
