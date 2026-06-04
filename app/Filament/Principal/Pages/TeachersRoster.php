<?php

namespace App\Filament\Principal\Pages;

use App\Models\Teacher;
use App\Models\TeacherContract;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

/**
 * Phase 5 — Teachers Roster.
 *
 * Cross-cutting read-only view of every teacher's current contract state.
 * Acts as the launch pad: each row links to the appropriate workspace
 * (Probation Watch / Contract Renewal / Contract Continuation / Teacher resource).
 */
class TeachersRoster extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Contract Management';
    protected static ?string $title = 'Teachers Roster';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.principal.pages.teachers-roster';

    #[Url]
    public ?string $tier = 'all'; // all | pkwt_1 | pkwt_2 | pkwt_3 | guru_sk | inactive

    #[Url]
    public ?string $search = '';

    public function mount(): void
    {
        $allowed = ['all', 'pkwt_1', 'pkwt_2', 'pkwt_3', 'guru_sk', 'inactive'];
        if (! in_array($this->tier, $allowed, true)) {
            $this->tier = 'all';
        }
    }

    /** Build the roster rows. */
    public function rows(): Collection
    {
        $teachers = Teacher::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy('name')
            ->get();

        $rows = $teachers->map(function (Teacher $t) {
            $current = TeacherContract::query()
                ->where('teacher_id', $t->id)
                ->where('status', 'active')
                ->orderByRaw('CASE WHEN ends_at IS NULL THEN 1 ELSE 0 END')
                ->orderBy('ends_at')
                ->first();

            $tier = $current?->type
                ?? (in_array($t->status, ['permanent', 'guru_sk'], true) ? 'guru_sk' : null);

            $isInactive = in_array($t->status, ['resigned', 'not_renewed', 'alumni'], true);

            $today = Carbon::today();
            $inProbation = $current
                && $current->type === 'pkwt_1'
                && $current->probation_starts_at
                && $current->probation_ends_at
                && $today->between(Carbon::parse($current->probation_starts_at), Carbon::parse($current->probation_ends_at));

            $inRenewal = $current
                && in_array($current->type, ['pkwt_1', 'pkwt_2', 'pkwt_3'], true)
                && $current->renewal_window_starts_at
                && $current->ends_at
                && $today->greaterThanOrEqualTo(Carbon::parse($current->renewal_window_starts_at))
                && $today->lessThanOrEqualTo(Carbon::parse($current->ends_at));

            // Pick destination page for the "Open" CTA.
            $destination = match (true) {
                $inProbation                          => 'probation-watch',
                $inRenewal                            => 'contract-renewal',
                $current && $current->type === 'guru_sk' => 'contract-continuation',
                default                               => 'teacher',
            };

            $daysLeft = $current && $current->ends_at
                ? (int) $today->diffInDays(Carbon::parse($current->ends_at), false)
                : null;

            return (object) [
                'teacher'      => $t,
                'contract'     => $current,
                'tier'         => $tier,         // pkwt_1|pkwt_2|pkwt_3|guru_sk|null
                'is_inactive'  => $isInactive,
                'in_probation' => $inProbation,
                'in_renewal'   => $inRenewal,
                'days_left'    => $daysLeft,
                'destination'  => $destination,
            ];
        });

        // Apply tier filter.
        $rows = $rows->filter(function ($r) {
            return match ($this->tier) {
                'all'      => true,
                'inactive' => $r->is_inactive,
                default    => $r->tier === $this->tier && ! $r->is_inactive,
            };
        })->values();

        // Sort: by ends_at asc, no-end (Guru SK) last, inactive at the very bottom.
        return $rows->sortBy([
            fn ($a, $b) => ($a->is_inactive <=> $b->is_inactive),
            fn ($a, $b) => (is_null($a->days_left) <=> is_null($b->days_left)),
            fn ($a, $b) => ($a->days_left <=> $b->days_left),
            fn ($a, $b) => strcmp($a->teacher->name, $b->teacher->name),
        ])->values();
    }

    public function counts(): array
    {
        $all = Teacher::all();

        $byTier = [
            'pkwt_1'  => 0,
            'pkwt_2'  => 0,
            'pkwt_3'  => 0,
            'guru_sk' => 0,
            'other'   => 0,
        ];

        $active = 0;
        $inactive = 0;
        $today = Carbon::today();
        $probation = 0;
        $renewal = 0;

        foreach ($all as $t) {
            $isInactive = in_array($t->status, ['resigned', 'not_renewed', 'alumni'], true);
            if ($isInactive) {
                $inactive++;
                continue;
            }

            $active++;

            $current = TeacherContract::query()
                ->where('teacher_id', $t->id)
                ->where('status', 'active')
                ->orderByRaw('CASE WHEN ends_at IS NULL THEN 1 ELSE 0 END')
                ->orderBy('ends_at')
                ->first();

            if ($current) {
                if (array_key_exists($current->type, $byTier)) {
                    $byTier[$current->type]++;
                } else {
                    $byTier['other']++;
                }

                if ($current->type === 'pkwt_1'
                    && $current->probation_starts_at
                    && $current->probation_ends_at
                    && $today->between(Carbon::parse($current->probation_starts_at), Carbon::parse($current->probation_ends_at))) {
                    $probation++;
                }

                if (in_array($current->type, ['pkwt_1', 'pkwt_2', 'pkwt_3'], true)
                    && $current->renewal_window_starts_at
                    && $current->ends_at
                    && $today->greaterThanOrEqualTo(Carbon::parse($current->renewal_window_starts_at))
                    && $today->lessThanOrEqualTo(Carbon::parse($current->ends_at))) {
                    $renewal++;
                }
            } elseif (in_array($t->status, ['permanent', 'guru_sk'], true)) {
                $byTier['guru_sk']++;
            } else {
                $byTier['other']++;
            }
        }

        return [
            'active'    => $active,
            'inactive'  => $inactive,
            'probation' => $probation,
            'renewal'   => $renewal,
            'by_tier'   => $byTier,
        ];
    }

    public function setTier(string $tier): void
    {
        $this->tier = $tier;
    }

    public function getViewData(): array
    {
        return [
            'rows'   => $this->rows(),
            'counts' => $this->counts(),
            'tier'   => $this->tier,
            'search' => $this->search,
            'tierLabels' => [
                'all'      => 'All',
                'pkwt_1'   => 'PKWT-I',
                'pkwt_2'   => 'PKWT-II',
                'pkwt_3'   => 'PKWT-III',
                'guru_sk'  => 'Guru SK',
                'inactive' => 'Inactive',
            ],
        ];
    }
}
