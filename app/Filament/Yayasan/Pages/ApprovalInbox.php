<?php

namespace App\Filament\Yayasan\Pages;

use App\Filament\Concerns\HasApprovalSheets;
use App\Models\Candidate;
use App\Models\ContractApprovalRequest;
use App\Models\Teacher;
use App\Models\TeacherContract;
use App\Models\TeacherObservation;
use App\Models\User;
use App\Services\ContractApprovalWorkflow;
use App\Support\ContractRequestPresenter;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

/**
 * Yayasan working queue: master list + detail pane + decision panel.
 */
class ApprovalInbox extends Page
{
    use HasApprovalSheets;

    protected static ?string $navigationIcon  = 'heroicon-o-inbox-stack';
    protected static ?string $navigationGroup = 'Approvals';
    protected static ?string $navigationLabel = 'Inbox';
    protected static ?string $title           = 'Approval Inbox';
    protected static ?int    $navigationSort  = 0;
    protected static string  $view            = 'filament.yayasan.pages.approval-inbox';

    public const QUEUES = [
        'decision' => ['label' => 'Needs decision',         'stages' => ['submitted']],
        'review'   => ['label' => 'In review',              'stages' => ['under_review']],
        'returned' => ['label' => 'Awaiting principal',     'stages' => ['correction_requested']],
        'assign'   => ['label' => 'Awaiting HR assignment', 'stages' => ['approved']],
        'prep'     => ['label' => 'In preparation',         'stages' => ['hr_assigned', 'hr_in_progress', 'document_uploaded', 'document_correction', 'document_accepted', 'signed_scheduled', 'hr_registration']],
        'closed'   => ['label' => 'Closed',                 'stages' => ['completed', 'rejected', 'cancelled']],
    ];

    #[Url(as: 'q')]
    public string $queue = 'decision';

    #[Url(as: 'type')]
    public string $typeFilter = 'all';

    #[Url(as: 'r')]
    public ?int $selectedId = null;

    public string $sort = 'oldest'; // oldest | urgent

    public int $limit = 0;

    // Assign / reassign form
    public ?int $hrUserId = null;
    public string $hrInstructions = '';
    public ?string $hrDueAt = null;
    public bool $assignAfterApprove = false;

    // Dossier slide-over
    public ?int $dossierTeacherId = null;
    public bool $dossierLoading = false;

    // Undo toast for optimistic Start review
    public ?int $undoReviewId = null;

    public function mount(): void
    {
        if (! array_key_exists($this->queue, self::QUEUES)) {
            $this->queue = 'decision';
        }
        $this->limit = (int) config('approvals.page_size', 20);
        if ($this->selectedId && ! ContractApprovalRequest::whereKey($this->selectedId)->exists()) {
            $this->selectedId = null;
        }
    }

    // ---------- Navigation ----------

    public function setQueue(string $q): void
    {
        if (array_key_exists($q, self::QUEUES)) {
            $this->queue = $q;
            $this->limit = (int) config('approvals.page_size', 20);
            $this->selectedId = null;
            unset($this->rows);
        }
    }

    public function setType(string $t): void
    {
        $this->typeFilter = in_array($t, ['all', 'pkwt_renewal', 'annual_continuation'], true) ? $t : 'all';
        $this->selectedId = null;
        unset($this->rows, $this->counts);
    }

    public function setSort(string $s): void
    {
        $this->sort = $s === 'urgent' ? 'urgent' : 'oldest';
        unset($this->rows);
    }

    public function select(int $id): void
    {
        $this->selectedId = $id;
        $this->closeSheet();
        unset($this->selected);
    }

    public function clearSelection(): void
    {
        $this->selectedId = null;
    }

    public function loadMore(): void
    {
        $this->limit += (int) config('approvals.page_size', 20);
        unset($this->rows);
    }

    /** Keyboard: move selection within the loaded list. */
    public function moveSelection(int $delta): void
    {
        $ids = $this->rows->take($this->limit)->pluck('id')->values();
        if ($ids->isEmpty()) return;
        $idx = $this->selectedId ? $ids->search($this->selectedId) : false;
        $next = $idx === false ? 0 : max(0, min($ids->count() - 1, $idx + $delta));
        $this->select($ids[$next]);
    }

    /** Keyboard shortcut dispatcher: only opens sheets that are valid right now. */
    public function shortcut(string $action): void
    {
        $sel = $this->selected;
        if (! $sel) return;
        $allowed = ContractRequestPresenter::for($sel)->availableActionsFor(auth()->user())->pluck('key');
        if (! $allowed->contains($action)) return;
        if ($action === 'start_review') { $this->startReview($sel->id); return; }
        $this->openSheet($sel->id, $action);
    }

    // ---------- Data ----------

    protected function baseQuery(): Builder
    {
        $q = ContractApprovalRequest::query();
        if ($this->typeFilter !== 'all') {
            $q->where('type', $this->typeFilter);
        }
        return $q;
    }

    #[Computed]
    public function counts(): array
    {
        $byStage = $this->baseQuery()->selectRaw('stage, count(*) as c')->groupBy('stage')->pluck('c', 'stage');
        $out = [];
        foreach (self::QUEUES as $k => $def) {
            $out[$k] = (int) collect($def['stages'])->sum(fn ($s) => $byStage[$s] ?? 0);
        }
        $out['hiring'] = Candidate::where('stage', 'yayasan')->count();
        return $out;
    }

    #[Computed]
    public function rows(): Collection
    {
        $stages = self::QUEUES[$this->queue]['stages'];
        $q = $this->baseQuery()
            ->whereIn('stage', $stages)
            ->with(['teacher', 'submitter', 'latestRevision', 'latestDocument', 'assignedHr', 'reviewer', 'events']);

        if ($this->queue === 'closed') {
            $q->orderByDesc('updated_at');
        } else {
            $q->orderBy('submitted_at');
        }

        $rows = $q->limit($this->limit + 1)->get();

        if ($this->sort === 'urgent' && $this->queue !== 'closed') {
            $rank = ['overdue' => 0, 'attention' => 1, 'none' => 2];
            $rows = $rows->sortBy(fn ($r) => $rank[ContractRequestPresenter::for($r)->urgency()] . '-' . str_pad((string) ($r->submitted_at?->timestamp ?? 0), 12, '0', STR_PAD_LEFT))->values();
        }

        return $rows;
    }

    public function hasMore(): bool
    {
        return $this->rows->count() > $this->limit;
    }

    #[Computed]
    public function selected(): ?ContractApprovalRequest
    {
        if (! $this->selectedId) return null;

        return ContractApprovalRequest::with([
            'teacher', 'submitter', 'reviewer', 'decider', 'assignedHr', 'approvedRevision',
            'revisions', 'latestRevision', 'latestDocument',
            'documentVersions.uploader', 'documentVersions.acceptor', 'documentVersions.signer',
            'events',
        ])->find($this->selectedId);
    }

    #[Computed]
    public function hrUsers(): Collection
    {
        $open = ContractApprovalRequest::activeForHr()->selectRaw('assigned_hr_id, count(*) as c')->groupBy('assigned_hr_id')->pluck('c', 'assigned_hr_id');

        return User::whereIn('role', ['hr', 'admin', 'superadmin'])->orderBy('name')->get()
            ->each(fn (User $u) => $u->setAttribute('open_tasks', (int) ($open[$u->id] ?? 0)));
    }

    public static function getNavigationBadge(): ?string
    {
        $n = ContractApprovalRequest::whereIn('stage', ['submitted'])->count() + Candidate::where('stage', 'yayasan')->count();
        return $n > 0 ? (string) $n : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    // ---------- Tier 0: Start review (optimistic, undoable) ----------

    public function startReview(int $id): void
    {
        $req = ContractApprovalRequest::find($id);
        if (! $req) return;
        try {
            app(ContractApprovalWorkflow::class)->startReview($req, auth()->user(), $req->stage);
            $this->undoReviewId = $id;
            $this->selectedId = $id;
            $this->queue = 'review';
            unset($this->rows, $this->counts, $this->selected);
        } catch (\Throwable $e) {
            Notification::make()->title('Could not start review')->body($e->getMessage())->danger()->send();
        }
    }

    public function undoStartReview(): void
    {
        if (! $this->undoReviewId) return;
        $req = ContractApprovalRequest::find($this->undoReviewId);
        $this->undoReviewId = null;
        if (! $req) return;
        try {
            app(ContractApprovalWorkflow::class)->undoStartReview($req, auth()->user());
            $this->queue = 'decision';
            unset($this->rows, $this->counts, $this->selected);
            Notification::make()->title('Review start undone')->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Could not undo')->body($e->getMessage())->danger()->send();
        }
    }

    public function dismissUndo(): void
    {
        $this->undoReviewId = null;
    }

    // ---------- Tier 1: Take over review ----------

    public function confirmTakeOver(): void
    {
        $req = $this->sheetRequest();
        if (! $req) return;
        $this->runGuarded(
            fn () => app(ContractApprovalWorkflow::class)->takeOverReview($req, auth()->user(), $this->seenStage),
            toast: 'You are now the reviewer.',
        );
        unset($this->rows, $this->selected);
    }

    // ---------- Tier 3: Approve (optionally assign HR in the same flow) ----------

    public function onSheetOpened(ContractApprovalRequest $request, string $action): void
    {
        if (in_array($action, ['approve', 'assign_hr', 'reassign_hr'], true)) {
            $this->hrUserId = null;
            $this->hrInstructions = '';
            $this->hrDueAt = now()->addWeekdays((int) config('approvals.hr_default_due_days', 7))->toDateString();
            $this->assignAfterApprove = false;
        }
    }

    public function confirmApprove(): void
    {
        $req = $this->sheetRequest();
        if (! $req) return;

        if ($this->assignAfterApprove) {
            if (! $this->hrUserId) { $this->sheetError = 'Choose an HR officer, or untick “Assign HR now”.'; return; }
            if (trim($this->hrInstructions) === '') { $this->sheetError = 'Add preparation instructions for HR.'; return; }
        }

        $ok = $this->runGuarded(function () use ($req) {
            $wf = app(ContractApprovalWorkflow::class);
            $wf->approve($req, auth()->user(), $this->seenStage);
            if ($this->assignAfterApprove) {
                $wf->assignHr($req, auth()->user(), (int) $this->hrUserId, trim($this->hrInstructions), $this->hrDueAt, 'approved');
            }
        }, success: $this->assignAfterApprove
            ? ['title' => 'Approved and assigned', 'body' => 'HR has been notified with your instructions. The principal can follow progress from My Submissions.']
            : ['title' => 'Approved', 'body' => 'The principal has been notified. Assign an HR officer so preparation can start.', 'next_label' => 'Assign HR now', 'next_action' => 'continueToAssign']);

        if ($ok) {
            unset($this->rows, $this->counts, $this->selected);
            $this->queue = $this->assignAfterApprove ? 'prep' : 'assign';
        }
    }

    /** From the approve success panel straight into the assign sheet. */
    public function continueToAssign(): void
    {
        $id = $this->sheetFor;
        $this->closeSheet();
        if ($id) $this->openSheet($id, 'assign_hr');
    }

    public function confirmReject(): void
    {
        $req = $this->sheetRequest();
        if (! $req) return;
        $ok = $this->runGuarded(
            fn () => app(ContractApprovalWorkflow::class)->reject($req, auth()->user(), $this->reason, $this->seenStage),
            success: ['title' => 'Request rejected', 'body' => 'The principal has been told why. The teacher\'s current contract is unchanged.'],
        );
        if ($ok) { unset($this->rows, $this->counts, $this->selected); $this->queue = 'closed'; }
    }

    // ---------- Tier 2 ----------

    public function confirmCorrection(): void
    {
        $req = $this->sheetRequest();
        if (! $req) return;
        $ok = $this->runGuarded(
            fn () => app(ContractApprovalWorkflow::class)->requestCorrection($req, auth()->user(), $this->reason, $this->seenStage),
            toast: 'Sent back to the principal for correction.',
        );
        if ($ok) { unset($this->rows, $this->counts, $this->selected); $this->queue = 'returned'; }
    }

    public function confirmAssign(): void
    {
        $req = $this->sheetRequest();
        if (! $req) return;
        if (! $this->hrUserId) { $this->sheetError = 'Choose an HR officer.'; return; }
        if (trim($this->hrInstructions) === '') { $this->sheetError = 'Add preparation instructions so HR knows what to prepare.'; return; }

        $ok = $this->runGuarded(
            fn () => app(ContractApprovalWorkflow::class)->assignHr($req, auth()->user(), (int) $this->hrUserId, trim($this->hrInstructions), $this->hrDueAt, $this->seenStage),
            toast: 'HR assigned and notified.',
        );
        if ($ok) { unset($this->rows, $this->counts, $this->selected); $this->queue = 'prep'; }
    }

    public function confirmReassign(): void
    {
        $req = $this->sheetRequest();
        if (! $req) return;
        if (! $this->hrUserId) { $this->sheetError = 'Choose the new HR officer.'; return; }
        $ok = $this->runGuarded(
            fn () => app(ContractApprovalWorkflow::class)->reassignHr($req, auth()->user(), (int) $this->hrUserId, $this->reason),
            toast: 'Task reassigned. Both HR officers were notified.',
        );
        if ($ok) unset($this->rows, $this->selected);
    }

    public function nudgeHr(int $id): void
    {
        $req = ContractApprovalRequest::find($id);
        if (! $req) return;
        try {
            app(ContractApprovalWorkflow::class)->nudgeHr($req, auth()->user());
            Notification::make()->title('Reminder sent to ' . ($req->assignedHr?->name ?? 'HR'))->success()->send();
            unset($this->selected);
        } catch (\Throwable $e) {
            Notification::make()->title('Reminder not sent')->body($e->getMessage())->warning()->send();
        }
    }

    // ---------- Dossier slide-over (lazy) ----------

    public function openDossier(int $teacherId): void
    {
        $this->dossierTeacherId = $teacherId;
        $this->dossierLoading = true;
        unset($this->dossier);
    }

    /** Called by wire:init once the slide-over skeleton has painted. */
    public function loadDossier(): void
    {
        $this->dossierLoading = false;
    }

    #[Computed]
    public function dossier(): array
    {
        if (! $this->dossierTeacherId || $this->dossierLoading) {
            return [];
        }

        $teacher = Teacher::find($this->dossierTeacherId);
        $observations = TeacherObservation::where('teacher_id', $this->dossierTeacherId)->orderByDesc('observed_at')->get();
        $contracts = TeacherContract::where('teacher_id', $this->dossierTeacherId)->orderByDesc('starts_at')->get();
        $history = ContractApprovalRequest::where('teacher_id', $this->dossierTeacherId)
            ->whereNotIn('stage', ['draft'])->with('decider')->orderByDesc('submitted_at')->limit(6)->get();

        return [
            'teacher'       => $teacher,
            'contracts'     => $contracts,
            'supervisions'  => $observations->where('observation_type', 'principal_supervision')->values(),
            'peers'         => $observations->where('observation_type', 'peer_observation')->values(),
            'total_obs'     => $observations->count(),
            'approved_obs'  => $observations->where('status', 'approved')->count(),
            'history'       => $history,
        ];
    }

    public function closeSlideOver(): void
    {
        $this->dossierTeacherId = null;
        $this->dossierLoading = false;
        unset($this->dossier);
    }

    public function getViewData(): array
    {
        $selected = $this->selected;
        return [
            'selectedRequest' => $selected,
            'presenter'       => $selected ? ContractRequestPresenter::for($selected) : null,
            'actions'         => $selected ? ContractRequestPresenter::for($selected)->availableActionsFor(auth()->user()) : collect(),
        ];
    }
}
