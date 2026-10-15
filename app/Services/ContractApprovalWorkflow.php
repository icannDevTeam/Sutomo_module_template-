<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\ContractApprovalRequest;
use App\Models\ContractDocumentVersion;
use App\Models\ContractRequestEvent;
use App\Models\ContractRequestRevision;
use App\Models\LetterOfIntent;
use App\Models\TeacherContract;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ContractApprovalWorkflow
{
    // ---------- Submission ----------

    public function submitFromContract(TeacherContract $contract, User $submitter, array $data): ContractApprovalRequest
    {
        return DB::transaction(function () use ($contract, $submitter, $data) {
            $this->assertNoDuplicateOpenRequest('pkwt_renewal', $contract->id, null);

            $request = ContractApprovalRequest::create([
                'type'               => 'pkwt_renewal',
                'teacher_id'         => $contract->teacher_id,
                'source_contract_id' => $contract->id,
                'submitted_by'       => $submitter->id,
                'campus'             => $submitter->campus,
                'submitted_at'       => now(),
                'stage'              => 'submitted',
            ]);

            $revision = ContractRequestRevision::create([
                'request_id'       => $request->id,
                'revision_number'  => 1,
                'recommendation'   => $data['recommendation'] ?? null,
                'justification'    => $data['justification'] ?? null,
                'current_type'     => $contract->type,
                'proposed_type'    => $data['proposed_type'] ?? null,
                'proposed_starts_at' => $data['proposed_starts_at'] ?? null,
                'proposed_ends_at' => $data['proposed_ends_at'] ?? null,
                'academic_year'    => $contract->academic_year,
                'evidence_paths'   => $data['evidence_paths'] ?? [],
                'submitted_by'     => $submitter->id,
                'submitted_at'     => now(),
            ]);

            // Mirror on the source contract so existing pages still function.
            $contract->update(['submitted_to_yayasan_at' => now()]);

            $this->logEvent($request, 'submission.created', $submitter, [
                'revision_id' => $revision->id,
                'note'        => 'Contract renewal submitted to Yayasan portal.',
                'to_stage'    => 'submitted',
            ]);

            $this->notifyYayasan(
                'New Renewal Submission',
                "Principal {$submitter->name} submitted a {$contract->type} renewal for {$contract->teacher?->name}."
            );

            return $request;
        });
    }

    public function submitFromLoi(LetterOfIntent $loi, User $submitter, array $data = []): ContractApprovalRequest
    {
        return DB::transaction(function () use ($loi, $submitter, $data) {
            $this->assertNoDuplicateOpenRequest('annual_continuation', null, $loi->id);

            $request = ContractApprovalRequest::create([
                'type'           => 'annual_continuation',
                'teacher_id'     => $loi->teacher_id,
                'source_loi_id'  => $loi->id,
                'submitted_by'   => $submitter->id,
                'campus'         => $submitter->campus,
                'submitted_at'   => now(),
                'stage'          => 'submitted',
            ]);

            $revision = ContractRequestRevision::create([
                'request_id'      => $request->id,
                'revision_number' => 1,
                'recommendation'  => 'continue',
                'justification'   => $data['justification'] ?? null,
                'academic_year'   => $loi->academic_year,
                'submitted_by'    => $submitter->id,
                'submitted_at'    => now(),
            ]);

            // Mirror on the LOI.
            $loi->update(['submitted_to_yayasan_at' => now()]);

            $this->logEvent($request, 'submission.created', $submitter, [
                'revision_id' => $revision->id,
                'note'        => 'Annual continuation submitted to Yayasan portal.',
                'to_stage'    => 'submitted',
            ]);

            $this->notifyYayasan(
                'New Continuation Submission',
                "Principal {$submitter->name} submitted a continuation request for {$loi->teacher?->name}."
            );

            return $request;
        });
    }

    // ---------- Yayasan actions ----------

    public function startReview(ContractApprovalRequest $request, User $actor, ?string $expectedStage = null): void
    {
        $this->guard($request, ['submitted', 'correction_requested'], $expectedStage);

        $from = $request->stage;
        $request->update([
            'stage'             => 'under_review',
            'review_started_at' => now(),
            'review_started_by' => $actor->id,
        ]);

        $this->logEvent($request, 'review.started', $actor, [
            'from_stage' => $from,
            'to_stage'   => 'under_review',
        ]);
    }

    /** Another Yayasan user takes over an in-progress review. */
    public function takeOverReview(ContractApprovalRequest $request, User $actor, ?string $expectedStage = null): void
    {
        $this->guard($request, ['under_review'], $expectedStage);

        $previous = $request->reviewer;
        $request->update([
            'review_started_at' => now(),
            'review_started_by' => $actor->id,
        ]);

        $this->logEvent($request, 'review.taken_over', $actor, [
            'from_stage' => 'under_review',
            'to_stage'   => 'under_review',
            'note'       => $previous ? "Taken over from {$previous->name}." : null,
        ]);
    }

    /** Undo for the optimistic Start review action (only by the same reviewer, only while under review). */
    public function undoStartReview(ContractApprovalRequest $request, User $actor): void
    {
        $this->guard($request, ['under_review'], null);
        if ($request->review_started_by !== $actor->id) {
            throw new \RuntimeException('Only the reviewer who started the review can undo it.');
        }

        $previousStage = $request->events()
            ->where('event_type', 'review.started')
            ->latest('occurred_at')
            ->first()?->from_stage ?? 'submitted';

        $request->update([
            'stage'             => $previousStage,
            'review_started_at' => null,
            'review_started_by' => null,
        ]);

        $this->logEvent($request, 'review.undone', $actor, [
            'from_stage' => 'under_review',
            'to_stage'   => $previousStage,
        ]);
    }

    public function requestCorrection(ContractApprovalRequest $request, User $actor, string $reason, ?string $expectedStage = null): void
    {
        $this->assertReason($reason);
        $this->guard($request, ['submitted', 'under_review'], $expectedStage);

        $from = $request->stage;
        $request->update(['stage' => 'correction_requested']);

        $this->logEvent($request, 'correction.requested', $actor, [
            'from_stage' => $from,
            'to_stage'   => 'correction_requested',
            'note'       => $reason,
        ]);

        $this->notifySubmitter($request, 'Submission Correction Required',
            "Yayasan has requested corrections to your submission for {$request->teacher?->name}: {$reason}",
            'warning'
        );
    }

    public function resubmit(ContractApprovalRequest $request, User $submitter, array $data): void
    {
        $this->guard($request, ['correction_requested'], null);
        if ($request->submitted_by !== $submitter->id && ! in_array($submitter->role, ['admin', 'superadmin'], true)) {
            throw new \RuntimeException('Only the original submitter can resubmit this request.');
        }

        $nextRevision = ($request->revisions()->max('revision_number') ?? 0) + 1;

        $revision = ContractRequestRevision::create([
            'request_id'       => $request->id,
            'revision_number'  => $nextRevision,
            'recommendation'   => $data['recommendation'] ?? null,
            'justification'    => $data['justification'] ?? null,
            'current_type'     => $data['current_type'] ?? null,
            'proposed_type'    => $data['proposed_type'] ?? null,
            'proposed_starts_at' => $data['proposed_starts_at'] ?? null,
            'proposed_ends_at' => $data['proposed_ends_at'] ?? null,
            'academic_year'    => $data['academic_year'] ?? null,
            'evidence_paths'   => $data['evidence_paths'] ?? [],
            'submitted_by'     => $submitter->id,
            'submitted_at'     => now(),
        ]);

        $request->update(['stage' => 'submitted']);

        $this->logEvent($request, 'submission.resubmitted', $submitter, [
            'revision_id' => $revision->id,
            'from_stage'  => 'correction_requested',
            'to_stage'    => 'submitted',
            'note'        => "Revision #{$nextRevision} submitted.",
        ]);

        $this->notifyYayasan(
            'Resubmission Received',
            "Principal {$submitter->name} resubmitted revision #{$nextRevision} for {$request->teacher?->name}."
        );
    }

    public function approve(ContractApprovalRequest $request, User $actor, ?string $expectedStage = null): void
    {
        DB::transaction(function () use ($request, $actor, $expectedStage) {
            $this->guard($request, ['submitted', 'under_review'], $expectedStage, lock: true);

            $latestRevision = $request->revisions()->latest('revision_number')->first();
            $from = $request->stage;

            $request->update([
                'stage'               => 'approved',
                'decision'            => 'approved',
                'decided_at'          => now(),
                'decided_by'          => $actor->id,
                'approved_revision_id' => $latestRevision?->id,
            ]);

            $this->logEvent($request, 'decision.approved', $actor, [
                'revision_id' => $latestRevision?->id,
                'from_stage'  => $from,
                'to_stage'    => 'approved',
            ]);
        });

        $this->notifySubmitter($request, 'Yayasan approved — ' . ($request->teacher?->name ?? 'request'),
            "Yayasan approved the submission for {$request->teacher?->name}. HR will be assigned to prepare the contract.",
            'success'
        );
    }

    public function reject(ContractApprovalRequest $request, User $actor, string $reason, ?string $expectedStage = null): void
    {
        $this->assertReason($reason);

        DB::transaction(function () use ($request, $actor, $reason, $expectedStage) {
            $this->guard($request, ['submitted', 'under_review'], $expectedStage, lock: true);

            $from = $request->stage;
            $request->update([
                'stage'           => 'rejected',
                'decision'        => 'rejected',
                'decided_at'      => now(),
                'decided_by'      => $actor->id,
                'decision_reason' => $reason,
            ]);

            $this->logEvent($request, 'decision.rejected', $actor, [
                'from_stage' => $from,
                'to_stage'   => 'rejected',
                'note'       => $reason,
            ]);
        });

        $this->notifySubmitter($request, 'Yayasan rejected — ' . ($request->teacher?->name ?? 'request'),
            "Yayasan rejected the submission for {$request->teacher?->name}: {$reason}. The current contract is unchanged.",
            'danger'
        );
    }

    /** Principal withdraws their own request before a decision. The source record is left untouched. */
    public function withdraw(ContractApprovalRequest $request, User $actor, string $reason): void
    {
        $this->assertReason($reason);
        $this->guard($request, ['submitted', 'under_review', 'correction_requested'], null);
        if ($request->submitted_by !== $actor->id && ! in_array($actor->role, ['admin', 'superadmin'], true)) {
            throw new \RuntimeException('Only the submitter can withdraw this request.');
        }

        $from = $request->stage;
        $request->update(['stage' => 'cancelled', 'decision_reason' => $reason]);

        $this->logEvent($request, 'request.withdrawn', $actor, [
            'from_stage' => $from,
            'to_stage'   => 'cancelled',
            'note'       => $reason,
        ]);

        $this->notifyYayasan('Submission withdrawn',
            "{$actor->name} withdrew the submission for {$request->teacher?->name}: {$reason}");
    }

    // ---------- HR Assignment ----------

    public function assignHr(ContractApprovalRequest $request, User $actor, int $hrUserId, string $instructions, ?string $dueAt, ?string $expectedStage = null): void
    {
        $this->guard($request, ['approved'], $expectedStage);
        if (trim($instructions) === '') {
            throw new \RuntimeException('Preparation instructions are required.');
        }

        $hr = User::findOrFail($hrUserId);
        $from = $request->stage;

        $request->update([
            'stage'                 => 'hr_assigned',
            'assigned_hr_id'        => $hrUserId,
            'hr_assigned_at'        => now(),
            'hr_assigned_by'        => $actor->id,
            'hr_instructions'       => $instructions,
            'hr_preparation_due_at' => $dueAt,
        ]);

        $this->logEvent($request, 'hr.assigned', $actor, [
            'from_stage' => $from,
            'to_stage'   => 'hr_assigned',
            'note'       => "Assigned to {$hr->name}. Instructions: {$instructions}",
        ]);

        $this->notifyUser($hr,
            'Contract Preparation Task Assigned',
            "Yayasan has assigned you to prepare a contract for {$request->teacher?->name}. Instructions: {$instructions}",
            'info'
        );

        $this->notifySubmitter($request, 'HR Assigned to Prepare Contract',
            "Yayasan has assigned {$hr->name} to prepare the contract for {$request->teacher?->name}.",
            'info'
        );
    }

    public function reassignHr(ContractApprovalRequest $request, User $actor, int $newHrUserId, string $reason): void
    {
        $this->assertReason($reason);
        $this->guard($request, ['hr_assigned', 'hr_in_progress', 'document_correction'], null);
        if ($request->assigned_hr_id === $newHrUserId) {
            throw new \RuntimeException('That HR user is already assigned.');
        }

        $oldHr = $request->assignedHr;
        $newHr = User::findOrFail($newHrUserId);
        $from  = $request->stage;

        $request->update([
            'assigned_hr_id'  => $newHrUserId,
            'hr_assigned_at'  => now(),
            'hr_assigned_by'  => $actor->id,
            'hr_instructions' => $reason,
            'stage'           => 'hr_assigned',
        ]);

        $this->logEvent($request, 'hr.reassigned', $actor, [
            'from_stage' => $from,
            'to_stage'   => 'hr_assigned',
            'note'       => "Reassigned from {$oldHr?->name} to {$newHr->name}. Reason: {$reason}",
        ]);

        if ($oldHr) {
            $this->notifyUser($oldHr, 'Task Reassigned', "Your contract preparation task for {$request->teacher?->name} has been reassigned.", 'warning');
        }
        $this->notifyUser($newHr, 'Contract Preparation Task Assigned',
            "You have been assigned to prepare a contract for {$request->teacher?->name}.", 'info');
    }

    /** Rate-limited reminder to the assigned HR user. */
    public function nudgeHr(ContractApprovalRequest $request, User $actor): void
    {
        $this->guard($request, ['hr_assigned', 'hr_in_progress', 'document_correction'], null);
        $hr = $request->assignedHr;
        if (! $hr) {
            throw new \RuntimeException('No HR user is assigned.');
        }

        $cooldown = (int) config('approvals.nudge_cooldown_minutes', 240);
        $last = $request->events()->where('event_type', 'nudge.sent')->latest('occurred_at')->first();
        if ($last && $last->occurred_at->gt(now()->subMinutes($cooldown))) {
            $next = $last->occurred_at->addMinutes($cooldown)->diffForHumans();
            throw new \RuntimeException("A reminder was sent {$last->occurred_at->diffForHumans()}. You can send another {$next}.");
        }

        $this->notifyUser($hr, 'Reminder: contract preparation for ' . ($request->teacher?->name ?? 'teacher'),
            "{$actor->name} sent a reminder. Due " . ($request->hr_preparation_due_at?->format('d M Y') ?? 'as soon as possible') . '.',
            'warning');

        $this->logEvent($request, 'nudge.sent', $actor, [
            'from_stage' => $request->stage,
            'to_stage'   => $request->stage,
            'note'       => "Reminder sent to {$hr->name}.",
        ]);
    }

    // ---------- HR Preparation ----------

    public function markHrInProgress(ContractApprovalRequest $request, User $hr): void
    {
        $this->guard($request, ['hr_assigned'], null);
        $this->assertHrAssignee($request, $hr);

        $request->update(['stage' => 'hr_in_progress']);

        $this->logEvent($request, 'hr.in_progress', $hr, [
            'from_stage' => 'hr_assigned',
            'to_stage'   => 'hr_in_progress',
        ]);
    }

    public function uploadDocument(
        ContractApprovalRequest $request,
        User $hr,
        string $storagePath,
        string $originalFilename,
        int $fileSize,
        string $mimeType,
        ?string $hash,
        string $note
    ): ContractDocumentVersion {
        $this->guard($request, ['hr_assigned', 'hr_in_progress', 'document_correction'], null);
        $this->assertHrAssignee($request, $hr);

        return DB::transaction(function () use ($request, $hr, $storagePath, $originalFilename, $fileSize, $mimeType, $hash, $note) {
            $nextVersion = ($request->documentVersions()->max('version_number') ?? 0) + 1;

            $doc = ContractDocumentVersion::create([
                'request_id'           => $request->id,
                'version_number'       => $nextVersion,
                'storage_path'         => $storagePath,
                'original_filename'    => $originalFilename,
                'mime_type'            => $mimeType,
                'file_size'            => $fileSize,
                'integrity_hash'       => $hash,
                'uploaded_by'          => $hr->id,
                'upload_note'          => $note,
                'uploaded_at'          => now(),
                'associated_revision_id' => $request->approved_revision_id,
            ]);

            $from = $request->stage;
            $request->update(['stage' => 'document_uploaded']);

            // Keep the source LOI in sync for the Contract Workflow detail.
            if ($request->type === 'annual_continuation' && $request->source_loi_id) {
                $request->sourceLoi?->update([
                    'yayasan_contract_path'         => $storagePath,
                    'yayasan_contract_uploaded_at'  => now(),
                    'yayasan_review_status'         => 'uploaded',
                ]);
            }

            $this->logEvent($request, 'document.uploaded', $hr, [
                'document_version_id' => $doc->id,
                'from_stage'          => $from,
                'to_stage'            => 'document_uploaded',
                'note'                => "v{$nextVersion}: {$originalFilename}",
            ]);

            $this->notifySubmitter($request, 'Contract Document Uploaded',
                "HR has uploaded the contract document for {$request->teacher?->name}. Please review and accept.",
                'info'
            );

            return $doc;
        });
    }

    // ---------- Principal Review ----------

    public function acceptDocument(ContractApprovalRequest $request, User $principal, ?string $expectedStage = null, ?int $expectedDocumentId = null): void
    {
        $this->guard($request, ['document_uploaded'], $expectedStage);
        if ($request->submitted_by !== $principal->id && ! in_array($principal->role, ['admin', 'superadmin'], true)) {
            throw new \RuntimeException('Only the submitting principal can accept this document.');
        }

        $doc = $request->latestDocument()->first();
        if (! $doc) {
            throw new \RuntimeException('No document found to accept.');
        }
        if ($expectedDocumentId && $doc->id !== $expectedDocumentId) {
            throw new StaleRequestException($request, $expectedStage);
        }

        DB::transaction(function () use ($request, $principal, $doc) {
            $doc->update([
                'accepted_at' => now(),
                'accepted_by' => $principal->id,
            ]);

            $request->update(['stage' => 'document_accepted']);

            // Mirror on source LOI.
            if ($request->type === 'annual_continuation' && $request->source_loi_id) {
                $request->sourceLoi?->update([
                    'yayasan_review_status'  => 'accepted',
                    'yayasan_reviewed_by'    => $principal->id,
                    'yayasan_reviewed_at'    => now(),
                ]);
            }

            $this->logEvent($request, 'document.accepted', $principal, [
                'document_version_id' => $doc->id,
                'from_stage'          => 'document_uploaded',
                'to_stage'            => 'document_accepted',
            ]);
        });
    }

    public function requestDocumentCorrection(ContractApprovalRequest $request, User $principal, string $notes, ?string $expectedStage = null): void
    {
        $this->assertReason($notes);
        $this->guard($request, ['document_uploaded'], $expectedStage);
        if ($request->submitted_by !== $principal->id && ! in_array($principal->role, ['admin', 'superadmin'], true)) {
            throw new \RuntimeException('Only the submitting principal can request a correction.');
        }

        $doc = $request->latestDocument()->first();

        DB::transaction(function () use ($request, $principal, $doc, $notes) {
            $doc?->update([
                'correction_note'         => $notes,
                'correction_requested_at' => now(),
            ]);

            $request->update(['stage' => 'document_correction']);

            // Mirror on source LOI.
            if ($request->type === 'annual_continuation' && $request->source_loi_id) {
                $request->sourceLoi?->update([
                    'yayasan_review_status'  => 'needs_revision',
                    'yayasan_resubmit_notes' => $notes,
                ]);
            }

            $this->logEvent($request, 'document.correction', $principal, [
                'document_version_id' => $doc?->id,
                'from_stage'          => 'document_uploaded',
                'to_stage'            => 'document_correction',
                'note'                => $notes,
            ]);

            if ($request->assignedHr) {
                $this->notifyUser($request->assignedHr,
                    'Document Correction Requested',
                    "Principal has requested corrections for the contract document of {$request->teacher?->name}: {$notes}",
                    'warning'
                );
            }
        });
    }

    // ---------- Signing ----------

    public function signDocument(ContractApprovalRequest $request, User $teacher, string $signatureText, string $ip): void
    {
        $this->guard($request, ['document_accepted'], null);
        if ($request->teacher?->user_id !== $teacher->id) {
            throw new \RuntimeException('Only the named teacher can sign this document.');
        }

        $doc = $request->documentVersions()->whereNotNull('accepted_at')->latest('version_number')->first();
        if (! $doc) {
            throw new \RuntimeException('No accepted document found to sign.');
        }

        DB::transaction(function () use ($request, $teacher, $doc, $signatureText, $ip) {
            $doc->update([
                'signed_at'       => now(),
                'signed_by'       => $teacher->id,
                'signature_text'  => $signatureText,
                'signature_ip'    => $ip,
            ]);

            // Mirror on source LOI.
            if ($request->type === 'annual_continuation' && $request->source_loi_id) {
                $request->sourceLoi?->update([
                    'agreement_signed_at'            => now(),
                    'agreement_signature_text'       => $signatureText,
                    'agreement_signature_ip'         => $ip,
                ]);
            }

            // PKWT renewals with a future effective date go to signed_scheduled.
            $newStage = 'hr_registration';
            if ($request->type === 'pkwt_renewal') {
                $proposedStart = $request->approvedRevision?->proposed_starts_at;
                if ($proposedStart && $proposedStart->isFuture()) {
                    $newStage = 'signed_scheduled';
                }
            }

            $request->update(['stage' => $newStage]);

            $this->logEvent($request, 'signing.completed', $teacher, [
                'document_version_id' => $doc->id,
                'from_stage'          => 'document_accepted',
                'to_stage'            => $newStage,
            ]);

            $this->notifySubmitter($request, 'Document Signed',
                "{$request->teacher?->name} has signed the contract document.",
                'success'
            );
        });
    }

    public function simulateTeacherSigning(ContractApprovalRequest $request, User $actor, string $ip): void
    {
        $this->guard($request, ['document_accepted'], 'document_accepted');
        if (! in_array($actor->role, [...User::HR_SIMULATION_ROLES, 'admin', 'superadmin'], true)) {
            throw new \RuntimeException('Only a Principal or administrator can run the teacher-signing simulation.');
        }

        $doc = $request->documentVersions()->whereNotNull('accepted_at')->latest('version_number')->first();
        if (! $doc) {
            throw new \RuntimeException('No accepted document found to simulate signing.');
        }

        DB::transaction(function () use ($request, $actor, $doc, $ip) {
            $teacherName = $request->teacher?->name ?? 'Teacher';
            $signatureText = 'DEMO SIMULATION — ' . $teacherName;
            $newStage = 'hr_registration';

            if ($request->type === 'pkwt_renewal') {
                $proposedStart = $request->approvedRevision?->proposed_starts_at;
                if ($proposedStart && $proposedStart->isFuture()) {
                    $newStage = 'signed_scheduled';
                }
            }

            $doc->update([
                'signed_at'      => now(),
                'signed_by'      => null,
                'signature_text' => $signatureText,
                'signature_ip'   => $ip,
            ]);

            if ($request->type === 'annual_continuation' && $request->source_loi_id) {
                $request->sourceLoi?->update([
                    'agreement_signed_at'       => now(),
                    'agreement_signature_text'  => $signatureText,
                    'agreement_signature_ip'    => $ip,
                ]);
            }

            $request->update(['stage' => $newStage]);

            $this->logEvent($request, 'signing.simulated', $actor, [
                'document_version_id' => $doc->id,
                'from_stage'          => 'document_accepted',
                'to_stage'            => $newStage,
                'note'                => "Demo only: simulated {$teacherName}'s signature. No teacher account or legal signature was used.",
            ]);

            $this->notifySubmitter($request, 'Demo signing simulated',
                "The demo signing step for {$teacherName}'s contract is complete. HR registration is next.",
                'info'
            );

            if ($request->assignedHr) {
                $this->notifyUser(
                    $request->assignedHr,
                    'Agreement handover recorded',
                    "The Principal has recorded the Agreement handover for {$teacherName}. Buku Induk registration is now ready to complete.",
                    'info'
                );
            }
        });
    }

    /** Principal completes Buku Induk for a continuation in the demo workflow. */
    public function recordBukuIndukByPrincipal(ContractApprovalRequest $request, User $actor): void
    {
        if (! in_array($actor->role, [...User::HR_SIMULATION_ROLES, 'admin', 'superadmin'], true)) {
            throw new \RuntimeException('Only the submitting Principal can record Buku Induk for this continuation.');
        }
        if ($request->submitted_by !== $actor->id && ! in_array($actor->role, ['admin', 'superadmin'], true)) {
            throw new \RuntimeException('Only the submitting Principal can record Buku Induk for this continuation.');
        }
        if ($request->type !== 'annual_continuation' || ! $request->source_loi_id) {
            throw new \RuntimeException('Buku Induk can only be recorded here for an annual continuation.');
        }

        DB::transaction(function () use ($request, $actor) {
            $this->guard($request, ['hr_registration'], 'hr_registration', lock: true);

            $request->update([
                'stage'        => 'completed',
                'completed_at' => now(),
            ]);

            $request->sourceLoi?->update([
                'buku_induk_recorded_at'    => now(),
                'continuation_completed_at' => now(),
                'hr_handoff_status'         => 'uploaded',
                'hr_uploaded_at'            => now(),
            ]);

            $this->logEvent($request, 'buku_induk.recorded', $actor, [
                'from_stage' => 'hr_registration',
                'to_stage'   => 'completed',
                'note'       => 'Buku Induk recorded by the submitting Principal.',
            ]);
        });

        $this->notifySubmitter($request, 'Buku Induk recorded',
            "The continuation for {$request->teacher?->name} is complete.",
            'success'
        );

        if ($request->assignedHr) {
            $this->notifyUser(
                $request->assignedHr,
                'Buku Induk registration completed',
                "The Principal recorded Buku Induk for {$request->teacher?->name}. No further HR registration action is required.",
                'success'
            );
        }
    }

    // ---------- HR Registration ----------

    public function acknowledgeHrRegistration(ContractApprovalRequest $request, User $hr): void
    {
        $this->guard($request, ['signed_scheduled', 'hr_registration'], null);
        $this->assertHrAssignee($request, $hr);

        $from = $request->stage;
        $request->update(['stage' => 'completed', 'completed_at' => now()]);

        // Mirror on source LOI.
        if ($request->type === 'annual_continuation' && $request->source_loi_id) {
            $request->sourceLoi?->update([
                'buku_induk_recorded_at'   => now(),
                'continuation_completed_at' => now(),
                'hr_handoff_status'        => 'uploaded',
                'hr_uploaded_at'           => now(),
            ]);
        }

        $this->logEvent($request, 'hr_registration.acknowledged', $hr, [
            'from_stage' => $from,
            'to_stage'   => 'completed',
        ]);

        $this->notifySubmitter($request, 'Completed',
            "HR has confirmed registration for {$request->teacher?->name}. The request is now complete.",
            'success'
        );
    }

    // ---------- Internal helpers ----------

    /**
     * Reload the request (optionally with a row lock), verify the caller's
     * expected stage (optimistic concurrency) and the allowed stage set.
     */
    private function guard(ContractApprovalRequest $request, array $allowed, ?string $expectedStage, bool $lock = false): void
    {
        $fresh = $lock
            ? ContractApprovalRequest::whereKey($request->id)->lockForUpdate()->first()
            : ContractApprovalRequest::find($request->id);

        if (! $fresh) {
            throw new \RuntimeException("Request #{$request->id} no longer exists.");
        }

        $request->setRawAttributes($fresh->getAttributes(), true);

        if ($expectedStage !== null && $request->stage !== $expectedStage) {
            throw new StaleRequestException($request, $expectedStage);
        }

        $this->assertStage($request, $allowed);
    }

    private function assertReason(string $reason): void
    {
        $min = (int) config('approvals.reason_min_length', 20);
        if (mb_strlen(trim($reason)) < $min) {
            throw new \InvalidArgumentException("Please give a reason of at least {$min} characters so the recipient understands what to do.");
        }
    }

    private function assertStage(ContractApprovalRequest $request, array $expected): void
    {
        if (! in_array($request->stage, $expected, true)) {
            throw new \RuntimeException(
                "Request #{$request->id} is in stage '{$request->stage}', expected one of: " . implode(', ', $expected)
            );
        }
    }

    private function assertHrAssignee(ContractApprovalRequest $request, User $hr): void
    {
        if ($request->assigned_hr_id === null) {
            throw new \RuntimeException("Request #{$request->id} has no HR assignee.");
        }

        $isPrincipalSimulation = in_array($hr->role, User::HR_SIMULATION_ROLES, true);
        if ($request->assigned_hr_id !== $hr->id && ! $isPrincipalSimulation) {
            throw new \RuntimeException("User {$hr->id} is not the assigned HR for request #{$request->id}.");
        }
    }

    private function assertNoDuplicateOpenRequest(string $type, ?int $contractId, ?int $loiId): void
    {
        $q = ContractApprovalRequest::where('type', $type)
            ->whereNotIn('stage', ['completed', 'cancelled', 'rejected']);

        if ($contractId) {
            $q->where('source_contract_id', $contractId);
        }
        if ($loiId) {
            $q->where('source_loi_id', $loiId);
        }

        if ($q->exists()) {
            throw new \RuntimeException('An active approval request already exists for this source record.');
        }
    }

    private function logEvent(ContractApprovalRequest $request, string $eventType, User $actor, array $extra = []): ContractRequestEvent
    {
        return ContractRequestEvent::create(array_merge([
            'request_id'  => $request->id,
            'event_type'  => $eventType,
            'actor_id'    => $actor->id,
            'actor_name'  => $actor->name,
            'actor_role'  => $actor->role ?? 'unknown',
            'occurred_at' => now(),
        ], $extra));
    }

    private function notifyYayasan(string $title, string $body): void
    {
        $yayasanUsers = User::whereIn('role', ['yayasan'])->get();
        foreach ($yayasanUsers as $user) {
            $this->notifyUser($user, $title, $body, 'info');
        }
    }

    private function notifySubmitter(ContractApprovalRequest $request, string $title, string $body, string $color = 'success'): void
    {
        $submitter = $request->submitter;
        if ($submitter) {
            $this->notifyUser($submitter, $title, $body, $color);
        }
    }

    public function notifyUser(User $user, string $title, string $body, string $color = 'success'): void
    {
        $n = Notification::make()->title($title)->body($body);
        match ($color) {
            'success' => $n->success(),
            'warning' => $n->warning(),
            'danger'  => $n->danger(),
            default   => $n->info(),
        };
        $n->sendToDatabase($user);
    }
}
