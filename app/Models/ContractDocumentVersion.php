<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractDocumentVersion extends Model
{
    protected $guarded = [];

    protected $casts = [
        'uploaded_at'              => 'datetime',
        'accepted_at'              => 'datetime',
        'correction_requested_at'  => 'datetime',
        'signed_at'                => 'datetime',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ContractApprovalRequest::class, 'request_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function acceptor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    public function associatedRevision(): BelongsTo
    {
        return $this->belongsTo(ContractRequestRevision::class, 'associated_revision_id');
    }

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null && $this->correction_requested_at === null;
    }

    public function isSigned(): bool
    {
        return $this->signed_at !== null;
    }

    public function needsCorrection(): bool
    {
        return $this->correction_requested_at !== null && $this->accepted_at === null;
    }
}
