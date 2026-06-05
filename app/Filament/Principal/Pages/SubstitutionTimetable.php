<?php

namespace App\Filament\Principal\Pages;

use App\Models\Teacher;
use App\Models\TeacherLeave;
use App\Support\SubstituteEligibilityGrid;
use App\Support\SubstituteSuggester;
use App\Support\SubstitutionTimetable as Builder;
use Carbon\CarbonImmutable;
use Filament\Pages\Page;

class SubstitutionTimetable extends Page
{
    protected static ?string $navigationGroup = 'Leave & Substitution';
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationLabel = 'Substitution Timetable';
    protected static ?string $title = 'Substitution Timetable';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.principal.pages.substitution-timetable';
    protected static ?string $slug = 'substitution-timetable';

    /** 'today' | 'tomorrow' | 'weekly' | 'monthly' */
    public string $activeView = 'weekly';
    public ?string $weekStart = null;
    public ?string $focusDate = null;
    public ?string $campus = null;
    public ?string $statusFilter = null;
    public ?int $eligibilityLeaveId = null;

    public function mount(): void
    {
        $today = CarbonImmutable::today();
        $this->focusDate = $this->focusDate ?: $today->toDateString();
        $this->weekStart = $this->weekStart ?: $today->startOfWeek()->toDateString();
    }

    public function setView(string $v): void
    {
        if (! in_array($v, ['today', 'tomorrow', 'weekly', 'monthly'], true)) {
            return;
        }
        $this->activeView = $v;

        $anchor = CarbonImmutable::parse($this->focusDate ?? CarbonImmutable::today()->toDateString());
        $this->weekStart = match ($this->activeView) {
            'monthly' => $anchor->startOfMonth()->toDateString(),
            default => $anchor->startOfWeek()->toDateString(),
        };
    }

    public function prevWindow(): void
    {
        $anchor = CarbonImmutable::parse($this->focusDate ?? CarbonImmutable::today()->toDateString());

        $this->focusDate = match ($this->activeView) {
            'today', 'tomorrow' => $anchor->subDay()->toDateString(),
            'monthly' => $anchor->subMonth()->toDateString(),
            default => $anchor->subWeek()->toDateString(),
        };

        $focus = CarbonImmutable::parse($this->focusDate);
        $this->weekStart = match ($this->activeView) {
            'monthly' => $focus->startOfMonth()->toDateString(),
            default => $focus->startOfWeek()->toDateString(),
        };
    }

    public function nextWindow(): void
    {
        $anchor = CarbonImmutable::parse($this->focusDate ?? CarbonImmutable::today()->toDateString());

        $this->focusDate = match ($this->activeView) {
            'today', 'tomorrow' => $anchor->addDay()->toDateString(),
            'monthly' => $anchor->addMonth()->toDateString(),
            default => $anchor->addWeek()->toDateString(),
        };

        $focus = CarbonImmutable::parse($this->focusDate);
        $this->weekStart = match ($this->activeView) {
            'monthly' => $focus->startOfMonth()->toDateString(),
            default => $focus->startOfWeek()->toDateString(),
        };
    }

    public function currentWindow(): void
    {
        $today = CarbonImmutable::today();
        $this->focusDate = $today->toDateString();
        $this->weekStart = match ($this->activeView) {
            'monthly' => $today->startOfMonth()->toDateString(),
            default => $today->startOfWeek()->toDateString(),
        };
    }

    public function updatedFocusDate($value): void
    {
        if (! $value) {
            return;
        }

        try {
            $focus = CarbonImmutable::parse((string) $value);
            $this->weekStart = match ($this->activeView) {
                'monthly' => $focus->startOfMonth()->toDateString(),
                default => $focus->startOfWeek()->toDateString(),
            };
        } catch (\Throwable $e) {
            // keep current state
        }
    }

    public function inspectLeave(int $leaveId): void
    {
        $this->eligibilityLeaveId = $leaveId;
        $this->dispatch('scroll-to-eligibility');
    }

    public function clearInspect(): void
    {
        $this->eligibilityLeaveId = null;
    }

    public function getSubtitle(): ?string
    {
        return 'Teacher-first roster view for approved leave coverage and standalone duties, with gaps and eligibility checks in one place.';
    }

    protected function getViewData(): array
    {
        $anchor = CarbonImmutable::parse($this->focusDate ?? CarbonImmutable::today()->toDateString());

        [$start, $end] = match ($this->activeView) {
            'today' => [$anchor->startOfDay(), $anchor->startOfDay()],
            'tomorrow' => [$anchor->addDay()->startOfDay(), $anchor->addDay()->startOfDay()],
            'monthly' => [$anchor->startOfMonth(), $anchor->endOfMonth()],
            default => [$anchor->startOfWeek(), $anchor->startOfWeek()->addDays(5)],
        };

        $data = Builder::buildWindow($start, $end, $this->campus ?: null, $this->statusFilter ?: null);

        // Build [day][period] grid for week/today views.
        $grid = [];
        foreach ($data['days'] as $d) {
            foreach ($data['periods'] as $p) {
                $grid[$d['date']][$p] = [];
            }
        }
        foreach ($data['cells'] as $cell) {
            if ($cell['period'] === '—' || ! isset($grid[$cell['date']])) {
                // Off-grid (duties without period). Render in a side list.
                continue;
            }
            $grid[$cell['date']][$cell['period']][] = $cell;
        }

        $offGrid = array_values(array_filter(
            $data['cells'],
            fn ($c) => $c['period'] === '—'
        ));

        $substituteIds = collect($data['cells'])
            ->pluck('substitute_id')
            ->filter()
            ->unique()
            ->values();

        $substitutes = Teacher::query()
            ->whereIn('id', $substituteIds)
            ->get()
            ->keyBy('id');

        $rosterRows = [];
        foreach ($data['cells'] as $cell) {
            $teacherId = $cell['substitute_id'] ?? null;
            if (! $teacherId) {
                continue;
            }

            if (! isset($rosterRows[$teacherId])) {
                $teacher = $substitutes->get($teacherId);
                $rosterRows[$teacherId] = [
                    'teacher_id'      => $teacherId,
                    'teacher_name'    => $teacher?->name ?? $cell['substitute'] ?? 'Unknown teacher',
                    'teacher_campus'   => $teacher?->campus,
                    'teacher_subject'  => $teacher?->subject,
                    'covered_count'    => 0,
                    'gap_count'        => 0,
                    'duty_count'       => 0,
                    'leave_count'      => 0,
                    'days'             => [],
                ];
            }

            $rosterRows[$teacherId]['days'][$cell['date']][] = $cell;
            $rosterRows[$teacherId]['covered_count']++;
            $rosterRows[$teacherId]['gap_count'] += (int) ($cell['gap'] ?? false);
            $rosterRows[$teacherId]['duty_count'] += $cell['source'] === 'duty' ? 1 : 0;
            $rosterRows[$teacherId]['leave_count'] += $cell['source'] === 'leave' ? 1 : 0;
        }

        foreach ($rosterRows as &$row) {
            foreach ($row['days'] as &$cells) {
                usort($cells, function (array $a, array $b): int {
                    $order = [
                        'leave' => 0,
                        'duty'  => 1,
                    ];

                    return [
                        $a['date'],
                        $order[$a['source']] ?? 99,
                        $a['session'] ?? '',
                        $a['period'] ?? '',
                        $a['subject'] ?? '',
                    ] <=> [
                        $b['date'],
                        $order[$b['source']] ?? 99,
                        $b['session'] ?? '',
                        $b['period'] ?? '',
                        $b['subject'] ?? '',
                    ];
                });
            }
            unset($cells);
        }
        unset($row);

        usort($rosterRows, function (array $a, array $b): int {
            return ($b['covered_count'] <=> $a['covered_count'])
                ?: ($a['gap_count'] <=> $b['gap_count'])
                ?: strcmp((string) $a['teacher_name'], (string) $b['teacher_name']);
        });

        // Inspectable leaves in window (approved or pending, requires substitute).
        $inspectableLeaves = TeacherLeave::query()
            ->with('teacher:id,name,subject,campus')
            ->whereIn('status', ['approved', 'pending'])
            ->where('starts_at', '<=', $end)
            ->where('ends_at', '>=', $start)
            ->orderBy('starts_at')
            ->get();

        // If a leave is selected, build the eligibility matrix.
        $eligibility    = null;
        $inspectedLeave = null;
        if ($this->eligibilityLeaveId) {
            $inspectedLeave = TeacherLeave::with('teacher')->find($this->eligibilityLeaveId);
            if ($inspectedLeave && $inspectedLeave->teacher) {
                $cands = SubstituteSuggester::for($inspectedLeave, 12);
                $eligibility = SubstituteEligibilityGrid::buildFor($inspectedLeave, $cands);
            }
        }

        return [
            'view'              => $this->activeView,
            'weekStart'         => $start->toDateString(),
            'weekEnd'           => $end->toDateString(),
            'focusDate'         => $anchor->toDateString(),
            'campus'            => $this->campus,
            'statusFilter'      => $this->statusFilter,
            'campuses'          => Builder::availableCampuses(),
            'data'              => $data,
            'grid'              => $grid,
            'offGrid'           => $offGrid,
            'rosterRows'        => $rosterRows,
            'rosterDays'        => $data['days'],
            'inspectableLeaves' => $inspectableLeaves,
            'inspectedLeave'    => $inspectedLeave,
            'eligibility'       => $eligibility,
        ];
    }
}
