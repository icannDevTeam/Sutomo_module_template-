<?php

namespace App\Filament\Principal\Pages;

use App\Filament\Concerns\HasApprovalSheets;
use App\Models\ContractApprovalRequest;
use App\Services\ContractApprovalWorkflow;
use App\Support\ContractRequestPresenter;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Deep-linkable detail of one submission with the principal's actions.
 * Route: /principal/my-submissions/{record}[?sheet=accept_document]
 */
class MySubmissionDetail extends Page
{
    use HasApprovalSheets;

    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $slug = 'my-submissions/{record}';
    protected static string $view = 'filament.principal.pages.my-submission-detail';

    public ContractApprovalRequest $record;

    /** Edit-and-resubmit form state. */
    public array $form = [
        'recommendation'     => null,
        'proposed_type'      => null,
        'proposed_starts_at' => null,
        'proposed_ends_at'   => null,
        'justification'      => '',
    ];

    /** Livewire binds the {record} route parameter straight into $record. */
    public function mount(): void
    {
        $this->authorizeView();

        $sheet = (string) request()->query('sheet', '');
        if ($sheet !== '' && array_key_exists($sheet, ContractRequestPresenter::ACTIONS)) {
            $allowed = ContractRequestPresenter::for($this->loadRecord())->availableActionsFor(auth()->user())->pluck('key');
            if ($allowed->contains($sheet)) {
                $this->openSheet($this->record->id, $sheet);
            }
        }
    }

    protected function authorizeView(): void
    {
        $user = auth()->user();
        $ok = $this->record->submitted_by === $user->id
            || in_array($user->role, ['admin', 'superadmin'], true);
        abort_unless($ok, 403, 'This submission belongs to another principal.');
    }

    public function getTitle(): string|Htmlable
    {
        return ($this->record->teacher?->name ?? 'Submission') . ' — ' . (ContractApprovalRequest::TYPES[$this->record->type] ?? '');
    }

    public function getBreadcrumbs(): array
    {
        return [
            MySubmissions::getUrl(panel: 'principal') => 'My Submissions',
            '#' => 'Request #' . $this->record->id,
        ];
    }

    /** Fresh record with every relation the detail needs. */
    public function loadRecord(): ContractApprovalRequest
    {
        $this->record = ContractApprovalRequest::with([
            'teacher', 'submitter', 'reviewer', 'decider', 'assignedHr', 'approvedRevision',
            'sourceLoi.teacher', 'sourceLoi.principal',
            'revisions', 'latestRevision', 'latestDocument',
            'documentVersions.uploader', 'documentVersions.acceptor', 'documentVersions.signer',
            'events',
        ])->findOrFail($this->record->id);

        return $this->record;
    }

    public function onSheetOpened(ContractApprovalRequest $request, string $action): void
    {
        if ($action === 'resubmit') {
            $rev = $request->latestRevision;
            $this->form = [
                'recommendation'     => $rev?->recommendation,
                'proposed_type'      => $rev?->proposed_type,
                'proposed_starts_at' => $rev?->proposed_starts_at?->toDateString(),
                'proposed_ends_at'   => $rev?->proposed_ends_at?->toDateString(),
                'justification'      => (string) ($rev?->justification ?? ''),
            ];
        }
    }

    // ---------- Actions ----------

    public function confirmAcceptDocument(): void
    {
        $req = $this->sheetRequest();
        if (! $req) return;

        $this->runGuarded(
            fn () => app(ContractApprovalWorkflow::class)->acceptDocument($req, auth()->user(), $this->seenStage, $this->seenDocId),
            success: [
                'title' => 'Document accepted',
                'body'  => ($req->teacher?->name ?? 'The teacher') . ' can now sign version ' . ($req->latestDocument?->version_number ?? '') . '. You will be notified when it is signed.',
            ],
        );
    }

    public function confirmDocumentCorrection(): void
    {
        $req = $this->sheetRequest();
        if (! $req) return;

        $this->runGuarded(
            fn () => app(ContractApprovalWorkflow::class)->requestDocumentCorrection($req, auth()->user(), $this->reason, $this->seenStage),
            toast: 'Correction requested — HR has been notified.',
        );
    }

    public function confirmResubmit(): void
    {
        $req = $this->sheetRequest();
        if (! $req) return;

        $data = $this->form;
        if (mb_strlen(trim((string) $data['justification'])) < (int) config('approvals.reason_min_length', 20)) {
            $this->sheetError = 'Please update the justification so Yayasan can see what changed (at least ' . config('approvals.reason_min_length', 20) . ' characters).';
            return;
        }
        if ($req->type === 'pkwt_renewal') {
            $allowed = array_keys(ContractRenewal::allowedNextTiers((string) $req->latestRevision?->current_type));
            if ($data['proposed_type'] && ! in_array($data['proposed_type'], $allowed, true)) {
                $this->sheetError = 'That tier is not a valid next step from the current contract.';
                return;
            }
            if ($data['proposed_starts_at'] && $data['proposed_ends_at'] && $data['proposed_ends_at'] < $data['proposed_starts_at']) {
                $this->sheetError = 'The proposed end date must be after the start date.';
                return;
            }
        }

        $payload = [
            'recommendation'     => $data['recommendation'] ?: $req->latestRevision?->recommendation,
            'justification'      => trim((string) $data['justification']),
            'current_type'       => $req->latestRevision?->current_type,
            'proposed_type'      => $data['proposed_type'] ?: $req->latestRevision?->proposed_type,
            'proposed_starts_at' => $data['proposed_starts_at'] ?: null,
            'proposed_ends_at'   => $data['proposed_ends_at'] ?: null,
            'academic_year'      => $req->latestRevision?->academic_year,
            'evidence_paths'     => $req->latestRevision?->evidence_paths ?? [],
        ];

        $this->runGuarded(
            fn () => app(ContractApprovalWorkflow::class)->resubmit($req, auth()->user(), $payload),
            success: ['title' => 'Resubmitted to Yayasan', 'body' => 'A new revision was recorded. The previous revision and Yayasan\'s note stay in the history.'],
        );
    }

    public function confirmWithdraw(): void
    {
        $req = $this->sheetRequest();
        if (! $req) return;

        $this->runGuarded(
            fn () => app(ContractApprovalWorkflow::class)->withdraw($req, auth()->user(), $this->reason),
            toast: 'Request withdrawn. The source record was not changed.',
        );
    }

    public function simulateAgreementSigning(): void
    {
        if ($this->record->type !== 'annual_continuation') {
            abort(403);
        }

        try {
            app(ContractApprovalWorkflow::class)->simulateTeacherSigning(
                $this->loadRecord(),
                auth()->user(),
                (string) request()->ip()
            );
        } catch (\RuntimeException $e) {
            Notification::make()->title($e->getMessage())->danger()->send();
            return;
        }

        Notification::make()
            ->title('Agreement handover recorded')
            ->body('Demo simulation only — this is not a legal teacher signature. HR has been notified.')
            ->success()
            ->send();
        $this->loadRecord();
    }

    public function recordBukuInduk(): void
    {
        if ($this->record->type !== 'annual_continuation') {
            abort(403);
        }

        try {
            app(ContractApprovalWorkflow::class)->recordBukuIndukByPrincipal(
                $this->loadRecord(),
                auth()->user()
            );
        } catch (\RuntimeException $e) {
            Notification::make()->title($e->getMessage())->danger()->send();
            return;
        }

        Notification::make()
            ->title('Buku Induk recorded')
            ->body('The continuation is complete, and HR has been notified.')
            ->success()
            ->send();
        $this->loadRecord();
    }

    /** Full LOI-to-Buku-Induk progress for annual continuations. */
    protected function continuationStages(ContractApprovalRequest $request): array
    {
        if ($request->type !== 'annual_continuation') {
            return [];
        }

        $current = match ($request->stage) {
            'submitted', 'under_review', 'correction_requested' => 3,
            'approved' => 4,
            'hr_assigned', 'hr_in_progress', 'document_correction' => 5,
            'document_uploaded' => 7,
            'document_accepted' => 8,
            'signed_scheduled', 'hr_registration' => 9,
            'completed' => 10,
            'rejected' => 3,
            'cancelled' => 2,
            default => 2,
        };
        $failedAt = in_array($request->stage, ['rejected', 'cancelled'], true) ? $current : null;
        $labels = [
            'LOI issued',
            'Teacher signed LOI',
            'Submitted to Yayasan',
            'Yayasan review',
            'HR assignment',
            'HR preparation',
            'Contract uploaded',
            'Principal review',
            'Agreement handover',
            'Buku Induk',
            'Complete',
        ];

        return collect($labels)
            ->map(fn (string $label, int $index) => [
                'label' => $label,
                'state' => $failedAt === $index
                    ? 'failed'
                    : ($request->stage === 'completed' || $index < $current
                        ? 'done'
                        : ($index === $current ? 'current' : 'locked')),
            ])
            ->all();
    }

    public function getViewData(): array
    {
        $record = $this->loadRecord();

        return [
            'record'    => $record,
            'presenter' => ContractRequestPresenter::for($record),
            'actions'   => ContractRequestPresenter::for($record)->availableActionsFor(auth()->user()),
            'tiers'     => $record->type === 'pkwt_renewal'
                ? ContractRenewal::allowedNextTiers((string) $record->latestRevision?->current_type)
                : [],
            'continuationStages' => $this->continuationStages($record),
        ];
    }
}
