<?php

namespace App\Filament\Principal\Pages;

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

    /** 'week' | 'today' | 'list' */
    public string $activeView = 'week';
    public ?string $weekStart = null;
    public ?string $campus = null;
    public ?string $statusFilter = null;
    public ?int $eligibilityLeaveId = null;

    public function mount(): void
    {
        if (! $this->weekStart) {
            $this->weekStart = CarbonImmutable::today()->startOfWeek()->toDateString();
        }
    }

    public function setView(string $v): void
    {
        if (! in_array($v, ['week', 'today', 'list'], true)) {
            return;
        }
        $this->activeView = $v;
    }

    public function prevWeek(): void
    {
        $this->weekStart = CarbonImmutable::parse($this->weekStart)->subWeek()->toDateString();
    }

    public function nextWeek(): void
    {
        $this->weekStart = CarbonImmutable::parse($this->weekStart)->addWeek()->toDateString();
    }

    public function thisWeek(): void
    {
        $this->weekStart = CarbonImmutable::today()->startOfWeek()->toDateString();
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
        return 'See every period currently being covered by a substitute — joined from approved leaves and duty assignments.';
    }

    protected function getViewData(): array
    {
        $weekStart = CarbonImmutable::parse($this->weekStart ?? CarbonImmutable::today()->startOfWeek()->toDateString())
            ->startOfWeek();

        if ($this->activeView === 'today') {
            $start = CarbonImmutable::today();
            $end   = $start;
        } else {
            $start = $weekStart;
            $end   = $weekStart->addDays(5); // Mon..Sat
        }

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
            'weekStart'         => $weekStart->toDateString(),
            'weekEnd'           => $weekStart->addDays(5)->toDateString(),
            'campus'            => $this->campus,
            'statusFilter'      => $this->statusFilter,
            'campuses'          => Builder::availableCampuses(),
            'data'              => $data,
            'grid'              => $grid,
            'offGrid'           => $offGrid,
            'inspectableLeaves' => $inspectableLeaves,
            'inspectedLeave'    => $inspectedLeave,
            'eligibility'       => $eligibility,
        ];
    }
}
