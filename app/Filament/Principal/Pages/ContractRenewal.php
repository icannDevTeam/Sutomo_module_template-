<?php

namespace App\Filament\Principal\Pages;

use App\Models\AuditLog;
use App\Models\DutyAssignment;
use App\Models\ObservationSetting;
use App\Models\TeacherContract;
use App\Models\TeacherObservation;
use App\Services\ContractApprovalWorkflow;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;
use Livewire\WithFileUploads;

/**
 * Phase 4 — Contract Renewal.
 *
 * Workspace for the Principal to manage PKWT-I → PKWT-II → PKWT-III → Guru SK
 * progression for teachers whose contract is inside the renewal window.
 *
 * Reads observation counts and submits renewal requests into the shared
 * approval workflow. Yayasan decisions and document preparation are tracked
 * from the linked submission rather than recorded by the Principal here.
 */
class ContractRenewal extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationGroup = 'Contract Management';
    protected static ?string $title = 'Contract Renewal';
    protected static ?int $navigationSort = 2; // between Probation Watch (1) and Contract Continuation (3)
    protected static string $view = 'filament.principal.pages.contract-renewal';

    #[Url]
    public ?string $tier = 'all'; // all | pkwt_1 | pkwt_2 | pkwt_3

    #[Url(as: 'ay')]
    public ?string $academicYear = null; // all | 2025/2026

    /** Per-row file uploads keyed by contract id. */
    public array $recommendationUploads = [];

    /** Per-row tier choice for PKWT-II → next (pkwt_3 | guru_sk). */
    public array $nextTierChoices = [];

    public function mount(): void
    {
        if (! in_array($this->tier, ['all', 'pkwt_1', 'pkwt_2', 'pkwt_3'], true)) {
            $this->tier = 'all';
        }

        $this->academicYear ??= DutyAssignment::currentAcademicYear();
    }

    public function selectAcademicYear(string $year): void
    {
        $this->academicYear = $year !== '' ? $year : 'all';
    }

    protected function rows(): Collection
    {
        $q = TeacherContract::query()
            ->inRenewalWindow()
            ->with(['teacher', 'latestApprovalRequest.latestDocument', 'latestApprovalRequest.assignedHr']);

        if ($this->tier !== 'all') {
            $q->where('type', $this->tier);
        }

        return $q->orderBy('ends_at')->get()->map(function (TeacherContract $c) {
            $teacher = $c->teacher;
            $ay = $c->academic_year ?: DutyAssignment::academicYearFor($c->starts_at);

            $supervisions = $teacher
                ? TeacherObservation::query()
                    ->where('teacher_id', $teacher->id)
                    ->forAcademicYear($ay)
                    ->byType('principal_supervision')
                    ->count()
                : 0;

            $peerObs = $teacher
                ? TeacherObservation::query()
                    ->where('teacher_id', $teacher->id)
                    ->forAcademicYear($ay)
                    ->byType('peer_observation')
                    ->count()
                : 0;

            $daysLeft = $c->ends_at ? (int) Carbon::today()->diffInDays($c->ends_at, false) : null;

            return (object) [
                'contract'     => $c,
                'teacher'      => $teacher,
                'supervisions' => $supervisions,
                'peer_obs'     => $peerObs,
                'days_left'    => $daysLeft,
                'academic_year'=> $ay,
            ];
        })->filter(function (object $row) {
            if ($this->academicYear === null || $this->academicYear === 'all') {
                return true;
            }

            return ($row->academic_year ?? null) === $this->academicYear;
        })->values();
    }

    protected function academicYearSummary(): array
    {
        return TeacherContract::query()
            ->inRenewalWindow()
            ->with('teacher')
            ->get()
            ->map(function (TeacherContract $c) {
                $ay = $c->academic_year ?: DutyAssignment::academicYearFor($c->starts_at);

                return [
                    'year' => $ay,
                    'pending' => is_null($c->recommendation_decision) ? 1 : 0,
                ];
            })
            ->filter(fn (array $row) => ! empty($row['year']))
            ->groupBy('year')
            ->map(fn ($group, $year) => [
                'year' => (string) $year,
                'total' => $group->count(),
                'active' => $group->count(),
                'pending' => (int) $group->sum('pending'),
            ])
            ->sortByDesc('year')
            ->values()
            ->all();
    }

    /** Allowed next tiers for a given current tier. */
    public static function allowedNextTiers(string $currentType): array
    {
        return match ($currentType) {
            'pkwt_1' => ['pkwt_2' => 'PKWT-II'],
            'pkwt_2' => ['pkwt_3' => 'PKWT-III', 'guru_sk' => 'Guru SK (Permanent)'],
            'pkwt_3' => ['guru_sk' => 'Guru SK (Permanent)'],
            default  => [],
        };
    }

    // ---------- Actions ----------

    public function uploadRecommendation(int $contractId): void
    {
        $c = $this->loadActive($contractId);
        if (! $c) {
            return;
        }
        $file = $this->recommendationUploads[$contractId] ?? null;
        if (! $file) {
            Notification::make()->title('Please choose a file first')->danger()->send();
            return;
        }
        $path = $file->store('teacher-contracts/recommendations', 'public');
        $c->update(['recommendation_letter_path' => $path]);
        unset($this->recommendationUploads[$contractId]);
        Notification::make()->title('Recommendation letter uploaded')->success()->send();
    }

    public function setRecommendation(int $contractId, string $decision): void
    {
        $c = $this->loadActive($contractId);
        if (! $c) {
            return;
        }
        if (! in_array($decision, ['renew', 'not_renew'], true)) {
            Notification::make()->title('Invalid decision')->danger()->send();
            return;
        }
        $c->update(['recommendation_decision' => $decision]);
        $this->writeAudit($c, 'recommendation.' . $decision, null);
        Notification::make()->title('Recommendation set: ' . str_replace('_', ' ', $decision))->success()->send();
    }

    public function submitToYayasan(int $contractId): void
    {
        $c = $this->loadActive($contractId);
        if (! $c) {
            return;
        }
        if (is_null($c->recommendation_decision)) {
            Notification::make()->title('Set the recommendation before submitting')->danger()->send();
            return;
        }
        if (! is_null($c->submitted_to_yayasan_at)) {
            Notification::make()->title('Already submitted')->warning()->send();
            return;
        }

        $proposedType = null;
        if ($c->recommendation_decision === 'renew') {
            $allowedNext = self::allowedNextTiers($c->type);
            $proposedType = $this->nextTierChoices[$contractId] ?? (count($allowedNext) === 1 ? array_key_first($allowedNext) : null);
            if (! $proposedType || ! array_key_exists($proposedType, $allowedNext)) {
                Notification::make()->title('Choose a valid next contract tier before submitting')->danger()->send();
                return;
            }
        }
        $evidencePaths = $c->recommendation_letter_path ? [$c->recommendation_letter_path] : [];

        try {
            app(ContractApprovalWorkflow::class)->submitFromContract($c, auth()->user(), [
                'recommendation' => $c->recommendation_decision,
                'proposed_type'  => $proposedType,
                'evidence_paths' => $evidencePaths,
            ]);
        } catch (\Exception $e) {
            Notification::make()->title('Submission failed: ' . $e->getMessage())->danger()->send();
            return;
        }

        $this->writeAudit($c, 'submitted_to_yayasan', null);
        Notification::make()->title('Submitted to Yayasan — track progress in My Submissions')->success()->send();
    }

    protected function loadActive(int $contractId): ?TeacherContract
    {
        $c = TeacherContract::query()->whereKey($contractId)->first();
        if (! $c) {
            Notification::make()->title('Contract not found')->danger()->send();
            return null;
        }
        if ($c->status !== 'active') {
            Notification::make()->title('This contract is no longer active')->warning()->send();
            return null;
        }
        return $c;
    }

    protected function writeAudit(TeacherContract $c, string $action, ?string $note): void
    {
        try {
            AuditLog::create([
                'occurred_at' => now(),
                'user_name'   => auth()->user()?->name ?? 'System',
                'role'        => 'Principal',
                'action'      => 'renewal.' . $action,
                'target'      => 'TeacherContract:' . $c->id,
                'from_value'  => $c->type,
                'to_value'    => $action,
                'note'        => $note,
            ]);
        } catch (\Throwable $e) {
            // best effort
        }
    }

    public function getViewData(): array
    {
        $rows = $this->rows();
        $settings = ObservationSetting::current();
        $dueDays = 30; // sensible default for contract renewal-due flag
        $currentAy = DutyAssignment::currentAcademicYear();
        $academicYearSummary = $this->academicYearSummary();

        $stats = [
            'total'    => $rows->count(),
            'due'      => $rows->filter(fn ($r) => ! is_null($r->days_left) && $r->days_left <= $dueDays)->count(),
            'pending'  => $rows->filter(fn ($r) => is_null($r->contract->recommendation_decision))->count(),
            'awaiting' => $rows->filter(fn ($r) => $r->contract->latestApprovalRequest?->isPendingYayasan()
                || (! $r->contract->latestApprovalRequest
                    && ! is_null($r->contract->submitted_to_yayasan_at)
                    && is_null($r->contract->yayasan_decision)))->count(),
        ];

        return [
            'rows'      => $rows,
            'stats'     => $stats,
            'dueDays'   => $dueDays,
            'tier'      => $this->tier,
            'academicYear' => $this->academicYear,
            'currentAy' => $currentAy,
            'academicYearSummary' => $academicYearSummary,
            'tierLabels' => [
                'all'    => 'All tiers',
                'pkwt_1' => 'PKWT-I',
                'pkwt_2' => 'PKWT-II',
                'pkwt_3' => 'PKWT-III',
            ],
        ];
    }
}
