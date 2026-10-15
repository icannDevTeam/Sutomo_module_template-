<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractRequestEvent extends Model
{
    protected $guarded = [];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    public const TYPES = [
        'submission.created'           => 'Submission Created',
        'submission.resubmitted'       => 'Resubmitted',
        'review.started'               => 'Review Started',
        'review.taken_over'            => 'Review Taken Over',
        'review.undone'                => 'Review Start Undone',
        'correction.requested'         => 'Correction Requested',
        'decision.approved'            => 'Approved',
        'decision.rejected'            => 'Rejected',
        'request.withdrawn'            => 'Withdrawn by Submitter',
        'hr.assigned'                  => 'HR Assigned',
        'hr.reassigned'                => 'HR Reassigned',
        'hr.in_progress'               => 'Preparation Started',
        'nudge.sent'                   => 'Reminder Sent',
        'document.uploaded'            => 'Document Uploaded',
        'document.correction'          => 'Document Correction Requested',
        'document.accepted'            => 'Document Accepted',
        'signing.completed'            => 'Document Signed',
        'signing.simulated'            => 'Teacher Signing Simulated',
        'buku_induk.recorded'          => 'Buku Induk Recorded',
        'activation.triggered'         => 'Contract Activated',
        'hr_registration.acknowledged' => 'HR Registration Acknowledged',
        'completion.marked'            => 'Completed',
        'cancellation.marked'          => 'Cancelled',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ContractApprovalRequest::class, 'request_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
