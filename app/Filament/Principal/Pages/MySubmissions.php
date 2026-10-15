<?php

namespace App\Filament\Principal\Pages;

use App\Models\ContractApprovalRequest;
use App\Models\LetterOfIntent;
use App\Models\User;
use App\Services\ContractApprovalWorkflow;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

/**
 * Principal submission tracker. List → detail; no actions on the list beyond
 * the single most relevant one, which deep-links into the detail page.
 */
class MySubmissions extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Approvals';
    protected static ?string $navigationLabel = 'Contract Workflow';
    protected static ?string $title = 'Contract Workflow';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.principal.pages.my-submissions';

    public const TABS = [
        'ready'    => ['label' => 'Ready to submit', 'stages' => []],
        'action'   => ['label' => 'Needs my action', 'stages' => ['document_uploaded', 'correction_requested']],
        'progress' => ['label' => 'In progress',      'stages' => ['submitted', 'under_review', 'approved', 'hr_assigned', 'hr_in_progress', 'document_correction', 'document_accepted', 'signed_scheduled', 'hr_registration']],
        'done'     => ['label' => 'Completed',        'stages' => ['completed']],
        'closed'   => ['label' => 'Closed',           'stages' => ['rejected', 'cancelled']],
    ];

    #[Url(as: 'tab')]
    public string $tab = 'action';

    #[Url(as: 'type')]
    public string $typeFilter = 'all';

    public int $limit = 0;

    public function mount(): void
    {
        if (! array_key_exists($this->tab, self::TABS)) {
            $this->tab = 'action';
        }
        $this->limit = (int) config('approvals.page_size', 20);

        // Land on "In progress" when nothing needs action.
        if ($this->tab === 'action' && ($this->counts()['action'] ?? 0) === 0 && ! request()->has('tab')) {
            $this->tab = ($this->counts()['ready'] ?? 0) > 0 ? 'ready' : 'progress';
        }
    }

    public function setTab(string $tab): void
    {
        if (array_key_exists($tab, self::TABS)) {
            $this->tab = $tab;
            $this->limit = (int) config('approvals.page_size', 20);
            unset($this->rows);
        }
    }

    public function submitContinuation(int $loiId): void
    {
        $loi = $this->readyContinuationQuery()->whereKey($loiId)->firstOrFail();

        try {
            app(ContractApprovalWorkflow::class)->submitFromLoi($loi, auth()->user());
        } catch (\RuntimeException $e) {
            Notification::make()->title('Submission could not be created')->body($e->getMessage())->danger()->send();
            return;
        }

        Notification::make()
            ->title('Continuation submitted to Yayasan')
            ->body('Track the request, review activity, and contract documents from this Contract Workflow page.')
            ->success()
            ->send();

        $this->tab = 'progress';
        $this->typeFilter = 'annual_continuation';
        unset($this->rows, $this->counts);
    }

    public function setType(string $type): void
    {
        $this->typeFilter = in_array($type, ['all', 'pkwt_renewal', 'annual_continuation'], true) ? $type : 'all';
        $this->limit = (int) config('approvals.page_size', 20);
        unset($this->rows, $this->counts);
    }

    public function loadMore(): void
    {
        $this->limit += (int) config('approvals.page_size', 20);
        unset($this->rows);
    }

    // ---------- Data ----------

    protected function scopedQuery(): Builder
    {
        /** @var User $user */
        $user = auth()->user();
        $q = ContractApprovalRequest::query();

        if (! in_array($user->role, ['admin', 'superadmin'], true)) {
            $q->where('submitted_by', $user->id);
        }
        if ($this->typeFilter !== 'all') {
            $q->where('type', $this->typeFilter);
        }

        return $q;
    }

    protected function readyContinuationQuery(): Builder
    {
        $user = auth()->user();

        return LetterOfIntent::query()
            ->where('status', 'signed')
            ->whereNull('submitted_to_yayasan_at')
            ->whereNull('continuation_completed_at')
            ->whereNull('archived_at')
            ->whereHas('teacher', fn (Builder $q) => $q->whereIn('status', ['guru_sk', 'permanent']))
            ->whereDoesntHave('latestApprovalRequest')
            ->when(
                ! in_array($user->role, ['admin', 'superadmin'], true),
                fn (Builder $q) => $q->where('principal_id', $user->id)
            )
            ->when(
                $this->typeFilter === 'pkwt_renewal',
                fn (Builder $q) => $q->whereRaw('1 = 0')
            );
    }

    /** One grouped query for all tab badges. */
    #[Computed]
    public function counts(): array
    {
        $byStage = $this->scopedQuery()
            ->selectRaw('stage, count(*) as c')
            ->groupBy('stage')
            ->pluck('c', 'stage');

        $out = [];
        foreach (self::TABS as $key => $def) {
            $out[$key] = (int) collect($def['stages'])->sum(fn ($s) => $byStage[$s] ?? 0);
        }
        $out['ready'] = $this->typeFilter === 'pkwt_renewal' ? 0 : $this->readyContinuationQuery()->count();

        return $out;
    }

    /** Only the active tab is loaded. */
    #[Computed]
    public function rows(): Collection
    {
        if ($this->tab === 'ready') {
            return $this->readyContinuationQuery()
                ->with(['teacher', 'principal'])
                ->orderByDesc('updated_at')
                ->limit($this->limit + 1)
                ->get();
        }

        $stages = self::TABS[$this->tab]['stages'];

        return $this->scopedQuery()
            ->whereIn('stage', $stages)
            ->with(['teacher', 'latestRevision', 'latestDocument', 'assignedHr', 'reviewer', 'submitter', 'events', 'sourceLoi'])
            ->orderBy('updated_at', $this->tab === 'action' ? 'asc' : 'desc')
            ->limit($this->limit + 1)
            ->get();
    }

    public function hasMore(): bool
    {
        return $this->rows->count() > $this->limit;
    }

    public static function getNavigationBadge(): ?string
    {
        $actionable = ContractApprovalRequest::where('submitted_by', auth()->id())
            ->whereIn('stage', self::TABS['action']['stages'])
            ->count();
        $ready = LetterOfIntent::query()
            ->where('status', 'signed')
            ->whereNull('submitted_to_yayasan_at')
            ->whereNull('continuation_completed_at')
            ->whereNull('archived_at')
            ->where('principal_id', auth()->id())
            ->whereHas('teacher', fn (Builder $q) => $q->whereIn('status', ['guru_sk', 'permanent']))
            ->whereDoesntHave('latestApprovalRequest')
            ->count();
        $n = $actionable + $ready;

        return $n > 0 ? (string) $n : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
