<?php

namespace App\Filament\Principal\Pages;

use App\Filament\Principal\Resources\LetterOfIntentResource;
use App\Models\LetterOfIntent;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

/**
 * Phase 2 redesign — per-teacher pipeline workspace for Guru SK continuation.
 *
 * KPIs:
 *   - Pending Agreement: contract uploaded by Yayasan but teacher hasn't signed the Agreement Letter yet.
 *   - In Progress with Yayasan: submitted, awaiting upload.
 *   - Handed Over: agreement signed (handover complete) or further along.
 *
 * Each row = one Guru SK teacher's LOI for the selected AY, with stage chips.
 */
class ContractContinuation extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document';
    protected static ?string $navigationGroup = 'Contract Management';
    protected static ?string $title = 'Contract Continuation';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.principal.pages.contract-continuation';

    #[Url]
    public ?string $academicYear = null;

    public array $resubmitNotes = [];

    public function mount(): void
    {
        $this->academicYear = LetterOfIntentResource::currentAcademicYear();
    }

    public function selectAcademicYear(string $year): void
    {
        // Contract continuation is intentionally locked to current AY only.
        $this->academicYear = LetterOfIntentResource::currentAcademicYear();
    }

    /**
     * Base query: only LOIs for Guru SK / permanent teachers in the selected AY.
     * Continuation is the per-AY ritual for already-permanent teachers.
     */
    protected function baseQuery(): Builder
    {
        $eligibleStatuses = ['guru_sk', 'permanent'];

        return LetterOfIntent::query()
            ->with(['teacher', 'principal'])
            ->whereHas('teacher', fn (Builder $q) => $q->whereIn('status', $eligibleStatuses))
            ->when($this->academicYear, fn (Builder $q) => $q->where('academic_year', $this->academicYear))
            ->whereNull('archived_at')
            ->orderByRaw('continuation_completed_at IS NULL DESC')
            ->orderByDesc('updated_at');
    }

    public function getStageFor(LetterOfIntent $loi): string
    {
        if (! is_null($loi->continuation_completed_at)) {
            return 'complete';
        }
        if (! is_null($loi->buku_induk_recorded_at)) {
            return 'buku_induk';
        }
        if (! is_null($loi->agreement_signed_at)) {
            return 'handed_over';
        }
        if ($loi->yayasan_review_status === 'needs_revision') {
            return 'needs_revision';
        }
        if (! is_null($loi->yayasan_contract_uploaded_at)) {
            if ($loi->yayasan_review_status === 'accepted') {
                return 'pending_agreement';
            }
            return 'contract_review';
        }
        if (! is_null($loi->submitted_to_yayasan_at)) {
            return 'in_progress_yayasan';
        }
        if ($loi->status === 'signed') {
            return 'awaiting_submission';
        }
        if ($loi->status === 'declined') {
            return 'declined';
        }
        return 'continuation_open';
    }

    public function acceptContract(int $loiId): void
    {
        $loi = $this->baseQuery()->whereKey($loiId)->firstOrFail();

        if (is_null($loi->yayasan_contract_uploaded_at)) {
            Notification::make()->title('Contract has not been uploaded yet')->danger()->send();
            return;
        }

        $loi->update([
            'yayasan_review_status' => 'accepted',
            'yayasan_review_notes'  => trim((string) ($this->resubmitNotes[$loiId] ?? 'Looks correct. Proceed to Agreement Letter signing.')),
            'yayasan_reviewed_by'   => auth()->id(),
            'yayasan_reviewed_at'   => now(),
        ]);

        Notification::make()->title('Contract accepted. Agreement Letter is now ready for signing.')->success()->send();
    }

    public function resubmitContract(int $loiId): void
    {
        $loi = $this->baseQuery()->whereKey($loiId)->firstOrFail();

        if (is_null($loi->yayasan_contract_uploaded_at)) {
            Notification::make()->title('Contract has not been uploaded yet')->danger()->send();
            return;
        }

        $note = trim((string) ($this->resubmitNotes[$loiId] ?? ''));
        if ($note === '') {
            Notification::make()->title('Please add a note before resubmitting to Yayasan')->danger()->send();
            return;
        }

        $loi->update([
            'yayasan_review_status' => 'needs_revision',
            'yayasan_resubmit_notes' => $note,
            'yayasan_reviewed_by'   => auth()->id(),
            'yayasan_reviewed_at'   => now(),
        ]);

        Notification::make()->title('Contract sent back to Yayasan for revision')->warning()->send();
    }

    public function recordBukuInduk(int $loiId): void
    {
        $loi = $this->baseQuery()->whereKey($loiId)->firstOrFail();
        if (is_null($loi->agreement_signed_at)) {
            Notification::make()->title('Teacher must sign the Agreement Letter first')->danger()->send();
            return;
        }
        $loi->update([
            'buku_induk_recorded_at' => now(),
            'hr_handoff_status'      => 'uploaded',
            'hr_uploaded_at'         => now(),
        ]);
        Notification::make()->title('Buku Induk recorded')->success()->send();
    }

    public function markComplete(int $loiId): void
    {
        $loi = $this->baseQuery()->whereKey($loiId)->firstOrFail();
        if (! $loi->canMarkContinuationComplete()) {
            Notification::make()->title('Cannot mark complete — earlier steps still missing')->danger()->send();
            return;
        }
        $loi->update(['continuation_completed_at' => now()]);
        Notification::make()->title('Continuation marked complete')->success()->send();
    }

    public function getViewData(): array
    {
        $currentAy = LetterOfIntentResource::currentAcademicYear();
        $academicYears = [$currentAy];

        $rows = $this->baseQuery()->get();

            // Derive summary directly from the already-loaded collection
            // to avoid MySQL GROUP BY restriction on multi-selectRaw without groupBy.
            $total = $rows->count();
            $pending = $rows->filter(fn ($r) => $r->status === 'signed')->count();
            $yearSummary = [[
                'year' => $currentAy,
                'total' => $total,
                'active' => $total,
                'pending' => $pending,
            ]];

        $stats = [
            'pending_agreement' => $rows->filter(fn ($r) => $this->getStageFor($r) === 'pending_agreement')->count(),
            'in_progress'       => $rows->filter(fn ($r) => $this->getStageFor($r) === 'in_progress_yayasan')->count(),
            'review_required'   => $rows->filter(fn ($r) => in_array($this->getStageFor($r), ['contract_review', 'needs_revision'], true))->count(),
            'handed_over'       => $rows->filter(fn ($r) => in_array($this->getStageFor($r), ['handed_over', 'buku_induk', 'complete'], true))->count(),
            'total'             => $rows->count(),
        ];

        return [
            'academicYears' => $academicYears,
            'academicYearSummary' => $yearSummary,
            'rows'          => $rows,
            'stats'         => $stats,
            'currentAy'     => $currentAy,
        ];
    }
}
