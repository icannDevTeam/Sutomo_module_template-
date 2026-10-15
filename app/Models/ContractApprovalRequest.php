<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ContractApprovalRequest extends Model
{
    protected $guarded = [];

    protected $casts = [
        'submitted_at'          => 'datetime',
        'review_started_at'     => 'datetime',
        'decided_at'            => 'datetime',
        'hr_assigned_at'        => 'datetime',
        'hr_preparation_due_at' => 'date',
        'completed_at'          => 'datetime',
        'is_legacy'             => 'boolean',
    ];

    public const TYPES = [
        'pkwt_renewal'       => 'PKWT Renewal',
        'annual_continuation' => 'Annual Continuation',
    ];

    public const STAGES = [
        'draft'                => 'Draft',
        'submitted'            => 'Submitted — Awaiting Yayasan',
        'under_review'         => 'Under Review',
        'correction_requested' => 'Submission Correction Required',
        'approved'             => 'Approved — Awaiting HR Assignment',
        'rejected'             => 'Rejected',
        'hr_assigned'          => 'Assigned — Not Started',
        'hr_in_progress'       => 'In Preparation',
        'document_uploaded'    => 'Uploaded — Ready for Your Review',
        'document_correction'  => 'Document Correction in Progress',
        'document_accepted'    => 'Ready for Teacher Signing',
        'signed_scheduled'     => 'Signed — Scheduled',
        'hr_registration'      => 'Awaiting HR Registration',
        'completed'            => 'Completed',
        'cancelled'            => 'Cancelled',
    ];

    public const STAGE_COLORS = [
        'draft'                => 'gray',
        'submitted'            => 'warning',
        'under_review'         => 'info',
        'correction_requested' => 'warning',
        'approved'             => 'success',
        'rejected'             => 'danger',
        'hr_assigned'          => 'warning',
        'hr_in_progress'       => 'info',
        'document_uploaded'    => 'info',
        'document_correction'  => 'warning',
        'document_accepted'    => 'success',
        'signed_scheduled'     => 'success',
        'hr_registration'      => 'info',
        'completed'            => 'success',
        'cancelled'            => 'gray',
    ];

    public const STAGE_OWNER = [
        'draft'                => 'Principal',
        'submitted'            => 'Yayasan',
        'under_review'         => 'Yayasan',
        'correction_requested' => 'Principal',
        'approved'             => 'Yayasan',
        'rejected'             => null,
        'hr_assigned'          => 'HR',
        'hr_in_progress'       => 'HR',
        'document_uploaded'    => 'Principal',
        'document_correction'  => 'HR',
        'document_accepted'    => 'Teacher',
        'signed_scheduled'     => 'System',
        'hr_registration'      => 'HR',
        'completed'            => null,
        'cancelled'            => null,
    ];

    // ---------- Relationships ----------

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'review_started_by');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function assignedHr(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_hr_id');
    }

    public function hrAssigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hr_assigned_by');
    }

    public function sourceContract(): BelongsTo
    {
        return $this->belongsTo(TeacherContract::class, 'source_contract_id');
    }

    public function sourceLoi(): BelongsTo
    {
        return $this->belongsTo(LetterOfIntent::class, 'source_loi_id');
    }

    public function resultingContract(): BelongsTo
    {
        return $this->belongsTo(TeacherContract::class, 'resulting_contract_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ContractRequestRevision::class, 'request_id')->orderBy('revision_number');
    }

    public function latestRevision(): HasOne
    {
        return $this->hasOne(ContractRequestRevision::class, 'request_id')->latestOfMany('revision_number');
    }

    public function approvedRevision(): BelongsTo
    {
        return $this->belongsTo(ContractRequestRevision::class, 'approved_revision_id');
    }

    public function documentVersions(): HasMany
    {
        return $this->hasMany(ContractDocumentVersion::class, 'request_id')->orderBy('version_number');
    }

    public function latestDocument(): HasOne
    {
        return $this->hasOne(ContractDocumentVersion::class, 'request_id')->latestOfMany('version_number');
    }

    public function events(): HasMany
    {
        return $this->hasMany(ContractRequestEvent::class, 'request_id')->orderBy('occurred_at');
    }

    // ---------- Scopes ----------

    public function scopePendingYayasanReview(Builder $q): Builder
    {
        return $q->whereIn('stage', ['submitted', 'under_review']);
    }

    public function scopeActiveForHr(Builder $q): Builder
    {
        return $q->whereIn('stage', ['hr_assigned', 'hr_in_progress', 'document_correction']);
    }

    public function scopeForHrUser(Builder $q, int $hrId): Builder
    {
        return $q->where('assigned_hr_id', $hrId);
    }

    public function scopeForSubmitter(Builder $q, int $userId): Builder
    {
        return $q->where('submitted_by', $userId);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->whereNotIn('stage', ['completed', 'cancelled', 'rejected']);
    }

    // ---------- Helpers ----------

    public function stageLabel(): string
    {
        return self::STAGES[$this->stage] ?? $this->stage;
    }

    public function stageColor(): string
    {
        return self::STAGE_COLORS[$this->stage] ?? 'gray';
    }

    public function nextOwner(): ?string
    {
        return self::STAGE_OWNER[$this->stage] ?? null;
    }

    public function isTerminal(): bool
    {
        return in_array($this->stage, ['completed', 'cancelled', 'rejected'], true);
    }

    public function isPendingYayasan(): bool
    {
        return in_array($this->stage, ['submitted', 'under_review'], true);
    }

    public function isHrActionable(): bool
    {
        return in_array($this->stage, ['hr_assigned', 'hr_in_progress', 'document_correction'], true)
            && $this->assigned_hr_id !== null;
    }
}
