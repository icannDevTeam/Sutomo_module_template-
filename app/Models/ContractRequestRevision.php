<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractRequestRevision extends Model
{
    protected $guarded = [];

    protected $casts = [
        'submitted_at'      => 'datetime',
        'proposed_starts_at' => 'date',
        'proposed_ends_at'  => 'date',
        'evidence_paths'    => 'array',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ContractApprovalRequest::class, 'request_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
