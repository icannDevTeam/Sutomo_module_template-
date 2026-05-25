<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensitiveApprovalRequest extends Model
{
    protected $guarded = [];

    protected $casts = [
        'payload'             => 'array',
        'first_approved_at'   => 'datetime',
        'second_approved_at'  => 'datetime',
    ];

    public const ACTION_TYPES = [
        'salary_change'      => 'Salary change',
        'contract_terminate' => 'Contract terminate',
        'title_demotion'     => 'Title demotion',
    ];

    public const STATUSES = [
        'pending_first'  => 'Pending 1st approval',
        'pending_second' => 'Pending 2nd approval',
        'approved'       => 'Approved',
        'rejected'       => 'Rejected',
    ];

    public const STATUS_COLORS = [
        'pending_first'  => 'warning',
        'pending_second' => 'info',
        'approved'       => 'success',
        'rejected'       => 'danger',
    ];

    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requester_id'); }
    public function targetTeacher(): BelongsTo { return $this->belongsTo(Teacher::class, 'target_teacher_id'); }
    public function firstApprover(): BelongsTo { return $this->belongsTo(User::class, 'first_approver_id'); }
    public function secondApprover(): BelongsTo { return $this->belongsTo(User::class, 'second_approver_id'); }
}
