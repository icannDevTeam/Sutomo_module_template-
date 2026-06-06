<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterOfIntent extends Model
{
    protected $table = 'letters_of_intent';

    protected $fillable = [
        'teacher_id', 'teacher_contract_id', 'principal_id', 'academic_year', 'position',
        'body', 'notes', 'deadline_at',
        'submitted_to_yayasan_at',
        'yayasan_contract_path',
        'yayasan_contract_uploaded_at',
        'yayasan_review_status',
        'yayasan_review_notes',
        'yayasan_resubmit_notes',
        'yayasan_reviewed_by',
        'yayasan_reviewed_at',
        'agreement_signed_at',
        'agreement_signature_text',
        'agreement_signature_ip',
        'buku_induk_recorded_at',
        'continuation_completed_at',
        'follow_up_status',
        'follow_up_outcome',
        'meeting_notes',
        'actions_taken',
        'resignation_letter_path',
        'resignation_letter_uploaded_at',
        'hr_handoff_status',
        'hr_handoff_marked_at',
        'hr_uploaded_at',
        'last_reminder_at',
        'archived_at',
    ];

    protected $casts = [
        'sent_at'                        => 'datetime',
        'signed_at'                      => 'datetime',
        'deadline_at'                    => 'datetime',
        'submitted_to_yayasan_at'        => 'datetime',
        'yayasan_contract_uploaded_at'   => 'datetime',
        'yayasan_reviewed_at'            => 'datetime',
        'agreement_signed_at'            => 'datetime',
        'buku_induk_recorded_at'         => 'datetime',
        'continuation_completed_at'      => 'datetime',
        'resignation_letter_uploaded_at' => 'datetime',
        'hr_handoff_marked_at'           => 'datetime',
        'hr_uploaded_at'                 => 'datetime',
        'last_reminder_at'               => 'datetime',
        'archived_at'                    => 'datetime',
    ];

    public const STATUSES = [
        'draft'    => 'Draft',
        'sent'     => 'Sent',
        'signed'   => 'Signed',
        'declined' => 'Declined',
        'expired'  => 'Expired',
    ];

    public const STATUS_COLORS = [
        'draft'    => 'gray',
        'sent'     => 'info',
        'signed'   => 'success',
        'declined' => 'danger',
        'expired'  => 'warning',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function principal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'principal_id');
    }

    public function teacherContract(): BelongsTo
    {
        return $this->belongsTo(TeacherContract::class);
    }

    public const FOLLOW_UP_STATUSES = [
        'meeting_logged'      => 'Meeting Logged',
        'resignation_pending' => 'Resignation Pending',
        'ready_for_hr'        => 'Ready for HR',
        'closed'              => 'Closed',
    ];

    public const FOLLOW_UP_OUTCOMES = [
        'continuing' => 'Continuing',
        'resigning'  => 'Resigning',
    ];

    public const HR_HANDOFF_STATUSES = [
        'ready'    => 'Ready for HR',
        'uploaded' => 'Uploaded to Buku Induk',
    ];

    public const YAYASAN_REVIEW_STATUSES = [
        'awaiting_upload' => 'Awaiting Yayasan Upload',
        'uploaded'        => 'Contract Uploaded',
        'accepted'        => 'Accepted by Principal',
        'needs_revision'  => 'Needs Revision',
    ];

    public function scopeForAcademicYear(Builder $q, $ay): Builder
    {
        return $q->where('academic_year', $ay);
    }

    public function scopeArchived(Builder $q): Builder
    {
        return $q->whereNotNull('archived_at');
    }

    public function scopeNotArchived(Builder $q): Builder
    {
        return $q->whereNull('archived_at');
    }

    public function scopeNeedsFollowUp(Builder $q): Builder
    {
        return $q->where('status', 'declined')
            ->where(function (Builder $sub) {
                $sub->whereNull('follow_up_status')
                    ->orWhereNotIn('follow_up_status', ['closed']);
            });
    }

    public function scopeContinuationPending(Builder $q): Builder
    {
        return $q->whereIn('status', ['draft', 'sent']);
    }

    public function scopeAwaitingYayasanSubmission(Builder $q): Builder
    {
        return $q->where('status', 'signed')
            ->whereNull('submitted_to_yayasan_at')
            ->whereNull('continuation_completed_at');
    }

    public function scopeContinuationComplete(Builder $q): Builder
    {
        return $q->whereNotNull('continuation_completed_at');
    }

    public function canCloseCase(): bool
    {
        return $this->status === 'declined'
            && ! is_null($this->resignation_letter_path)
            && ! is_null($this->meeting_notes);
    }

    public function canMarkContinuationComplete(): bool
    {
        return $this->status === 'signed'
            && ! is_null($this->submitted_to_yayasan_at)
            && ! is_null($this->yayasan_contract_uploaded_at)
            && $this->yayasan_review_status === 'accepted'
            && ! is_null($this->agreement_signed_at)
            && ! is_null($this->buku_induk_recorded_at)
            && is_null($this->continuation_completed_at);
    }

    public function isAwaitingYayasanUpload(): bool
    {
        return ! is_null($this->submitted_to_yayasan_at)
            && is_null($this->yayasan_contract_uploaded_at);
    }

    public function canOpenAgreementLetter(): bool
    {
        return ! is_null($this->yayasan_contract_uploaded_at)
            && $this->yayasan_review_status === 'accepted'
            && is_null($this->agreement_signed_at);
    }

    public function isArchived(): bool
    {
        return ! is_null($this->archived_at);
    }

    public function isNotArchived(): bool
    {
        return is_null($this->archived_at);
    }
}
