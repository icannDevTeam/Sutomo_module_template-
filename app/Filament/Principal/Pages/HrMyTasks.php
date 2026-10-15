<?php

namespace App\Filament\Principal\Pages;

use App\Filament\Concerns\HasApprovalSheets;
use App\Models\ContractApprovalRequest;
use App\Models\User;
use App\Services\ContractApprovalWorkflow;
use App\Support\ContractRequestPresenter;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\WithFileUploads;

/**
 * HR workspace: read-only visibility of submissions awaiting Yayasan,
 * the officer's own preparation tasks, submitted documents and
 * post-signing registration acknowledgements.
 */
class HrMyTasks extends Page
{
    use HasApprovalSheets;
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'HR Tasks';
    protected static ?string $title = 'HR Coordination';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.principal.pages.hr-my-tasks';

    public const TABS = [
        'tasks'        => ['label' => 'My tasks'],
        'submitted'    => ['label' => 'Submitted documents'],
        'registration' => ['label' => 'Registration required'],
        'awaiting'     => ['label' => 'Awaiting Yayasan'],
    ];

    #[Url(as: 'tab')]
    public string $tab = 'tasks';

    #[Url(as: 'r')]
    public ?int $selectedId = null;

    /** Temporary upload bound in the upload sheet. */
    public $upload = null;
    public string $uploadNote = '';

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, [...User::HR_SIMULATION_ROLES, 'admin', 'superadmin'], true);
    }

    public function isPrincipalSimulation(): bool
    {
        return in_array(auth()->user()?->role, User::HR_SIMULATION_ROLES, true);
    }

    private function taskQuery(): Builder
    {
        $query = ContractApprovalRequest::query();

        if ($this->isPrincipalSimulation()) {
            return $query->whereNotNull('assigned_hr_id');
        }

        return $query->forHrUser(auth()->id());
    }

    public function mount(): void
    {
        if (! array_key_exists($this->tab, self::TABS)) {
            $this->tab = 'tasks';
        }
    }

    public function setTab(string $tab): void
    {
        if (array_key_exists($tab, self::TABS)) {
            $this->tab = $tab;
            $this->selectedId = null;
            unset($this->rows);
        }
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

    // ---------- Data ----------

    #[Computed]
    public function counts(): array
    {
        return [
            'tasks'        => $this->taskQuery()->activeForHr()->count(),
            'submitted'    => $this->taskQuery()->whereIn('stage', ['document_uploaded', 'document_accepted'])->count(),
            'registration' => $this->taskQuery()->whereIn('stage', ['hr_registration', 'signed_scheduled'])->count(),
            'awaiting'     => ContractApprovalRequest::whereIn('stage', ['submitted', 'under_review', 'correction_requested', 'approved'])->count(),
        ];
    }

    #[Computed]
    public function rows(): Collection
    {
        $with = ['teacher', 'submitter', 'latestRevision', 'latestDocument', 'assignedHr', 'reviewer', 'events'];

        return match ($this->tab) {
            'tasks'        => $this->taskQuery()->activeForHr()->with($with)->orderBy('hr_preparation_due_at')->get(),
            'submitted'    => $this->taskQuery()->whereIn('stage', ['document_uploaded', 'document_accepted'])->with($with)->latest('updated_at')->limit(50)->get(),
            'registration' => $this->taskQuery()->whereIn('stage', ['hr_registration', 'signed_scheduled'])->with($with)->orderBy('updated_at')->get(),
            default        => ContractApprovalRequest::whereIn('stage', ['submitted', 'under_review', 'correction_requested', 'approved'])->with($with)->orderBy('submitted_at')->limit(50)->get(),
        };
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

    public static function getNavigationBadge(): ?string
    {
        $query = ContractApprovalRequest::query();
        if (! in_array(auth()->user()?->role, User::HR_SIMULATION_ROLES, true)) {
            $query->forHrUser(auth()->id());
        }
        $n = $query->activeForHr()->count();
        return $n > 0 ? (string) $n : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    // ---------- Actions ----------

    public function onSheetOpened(ContractApprovalRequest $request, string $action): void
    {
        if ($action === 'upload_document') {
            $this->upload = null;
            $this->uploadNote = '';
        }
    }

    /** Tier 0 — immediate. */
    public function markInProgress(int $id): void
    {
        $req = ContractApprovalRequest::find($id);
        if (! $req) return;
        try {
            app(ContractApprovalWorkflow::class)->markHrInProgress($req, auth()->user());
            unset($this->rows, $this->counts, $this->selected);
            Notification::make()->title('Preparation started')->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Could not start')->body($e->getMessage())->danger()->send();
        }
    }

    /** Tier 2 — upload sheet with file preview. */
    public function confirmUpload(): void
    {
        $req = $this->sheetRequest();
        if (! $req) return;

        if (! $this->upload) {
            $this->sheetError = 'Choose a PDF to upload.';
            return;
        }
        $mime = $this->upload->getMimeType() ?? '';
        if ($mime !== 'application/pdf') {
            $this->sheetError = 'Only PDF files are accepted (received ' . ($mime ?: 'unknown type') . ').';
            return;
        }
        if ($this->upload->getSize() > 15 * 1024 * 1024) {
            $this->sheetError = 'The file is larger than 15 MB.';
            return;
        }

        $file = $this->upload;
        $ok = $this->runGuarded(function () use ($req, $file) {
            $hash = hash_file('sha256', $file->getRealPath());
            $path = $file->storeAs(
                "contract-documents/{$req->id}",
                now()->format('Ymd-His') . '-' . \Illuminate\Support\Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.pdf',
                config('approvals.document_disk', 'local')
            );

            app(ContractApprovalWorkflow::class)->uploadDocument(
                $req, auth()->user(), $path,
                $file->getClientOriginalName(), (int) $file->getSize(), 'application/pdf', $hash,
                trim($this->uploadNote) !== '' ? trim($this->uploadNote) : 'Uploaded by ' . auth()->user()->name,
            );
        }, success: [
            'title' => 'Document submitted for review',
            'body'  => 'The principal has been notified. If they request changes, the task returns to you with their note.',
        ]);

        if ($ok) {
            $this->upload = null;
            $this->uploadNote = '';
            unset($this->rows, $this->counts, $this->selected);
        }
    }

    /** Tier 3 — confirms Buku Induk registration. */
    public function confirmRegistration(): void
    {
        $req = $this->sheetRequest();
        if (! $req) return;
        $ok = $this->runGuarded(
            fn () => app(ContractApprovalWorkflow::class)->acknowledgeHrRegistration($req, auth()->user()),
            success: ['title' => 'Registration confirmed', 'body' => 'The request is complete and the principal has been notified.'],
        );
        if ($ok) unset($this->rows, $this->counts, $this->selected);
    }

    public function getViewData(): array
    {
        $selected = $this->selected;
        $actions = $selected
            ? ContractRequestPresenter::for($selected)->availableActionsFor(auth()->user())
                ->whereIn('key', ['mark_in_progress', 'upload_document', 'acknowledge_registration'])
                ->values()
            : collect();

        return [
            'selectedRequest' => $selected,
            'actions'         => $actions,
            'isSimulation'    => $this->isPrincipalSimulation(),
        ];
    }
}
