<?php

namespace App\Filament\Principal\Pages;

use App\Models\Teacher;
use App\Support\SubstitutionTimetable as Builder;
use Carbon\CarbonImmutable;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class SubstitutionTimetable extends Page
{
    protected static ?string $navigationGroup = 'Leave & Substitution';
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationLabel = 'Substitution Timetable';
    protected static ?string $title = 'Substitution Timetable';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.principal.pages.substitution-timetable';
    protected static ?string $slug = 'substitution-timetable';

    public const VIEWS = ['day', 'week', 'month'];
    public const LAYOUTS = ['roster', 'grid'];

    /** 'day' | 'week' | 'month' */
    #[Url(as: 'view')]
    public string $activeView = 'week';

    /** 'roster' (teacher × day) | 'grid' (period × day). Month always uses the calendar. */
    #[Url(as: 'layout')]
    public string $display = 'roster';

    #[Url(as: 'date')]
    public ?string $focusDate = null;

    #[Url]
    public ?string $campus = null;

    #[Url]
    public ?string $unit = null;

    #[Url(as: 'show')]
    public ?string $statusFilter = null;

    /** Teacher name search (absent or covering teacher, or duty title). */
    #[Url(as: 'q')]
    public ?string $search = null;

    public function mount(): void
    {
        if (! in_array($this->activeView, self::VIEWS, true)) {
            $this->activeView = 'week';
        }
        if (! in_array($this->display, self::LAYOUTS, true)) {
            $this->display = 'roster';
        }
        $this->focusDate = $this->anchor()->toDateString();
    }

    public function setView(string $v): void
    {
        if (in_array($v, self::VIEWS, true)) {
            $this->activeView = $v;
        }
    }

    public function setLayout(string $l): void
    {
        if (in_array($l, self::LAYOUTS, true)) {
            $this->display = $l;
        }
    }

    public function goToday(): void
    {
        $this->focusDate = CarbonImmutable::today()->toDateString();
        if ($this->activeView === 'month') {
            $this->activeView = 'day';
        }
    }

    public function goTomorrow(): void
    {
        $this->focusDate = self::skipSunday(CarbonImmutable::today()->addDay(), 1)->toDateString();
        $this->activeView = 'day';
    }

    /** Drill into a single day (from the month calendar or a column header). */
    public function openDay(string $date): void
    {
        try {
            $this->focusDate = CarbonImmutable::parse($date)->toDateString();
            $this->activeView = 'day';
        } catch (\Throwable $e) {
            // ignore malformed input
        }
    }

    public function prevWindow(): void
    {
        $this->shiftWindow(-1);
    }

    public function nextWindow(): void
    {
        $this->shiftWindow(1);
    }

    protected function shiftWindow(int $dir): void
    {
        $a = $this->anchor();
        $a = match ($this->activeView) {
            'day'   => self::skipSunday($a->addDays($dir), $dir),
            'month' => $a->startOfMonth()->addMonths($dir),
            default => $a->addWeeks($dir),
        };
        $this->focusDate = $a->toDateString();
    }

    protected static function skipSunday(CarbonImmutable $d, int $dir): CarbonImmutable
    {
        return $d->isSunday() ? $d->addDays($dir >= 0 ? 1 : -1) : $d;
    }

    public function updatedFocusDate($value): void
    {
        $this->focusDate = $this->anchor()->toDateString();
    }

    public function updatedStatusFilter($value): void
    {
        if ($value && ! isset(Builder::FILTERS[$value])) {
            $this->statusFilter = null;
        }
    }

    public function resetFilters(): void
    {
        $this->campus = null;
        $this->unit = null;
        $this->statusFilter = null;
        $this->search = null;
    }

    public function getSubtitle(): ?string
    {
        return 'Who is covering which lesson, which duties are running, and which gaps still need a substitute or hand-off.';
    }

    protected function anchor(): CarbonImmutable
    {
        try {
            return $this->focusDate
                ? CarbonImmutable::parse($this->focusDate)->startOfDay()
                : CarbonImmutable::today();
        } catch (\Throwable $e) {
            return CarbonImmutable::today();
        }
    }

    /** @return array{0: CarbonImmutable, 1: CarbonImmutable} */
    protected function windowBounds(): array
    {
        $a = $this->anchor();

        return match ($this->activeView) {
            'day'   => [$a, $a],
            'month' => [$a->startOfMonth(), $a->endOfMonth()->startOfDay()],
            default => [$a->startOfWeek(), $a->startOfWeek()->addDays(5)],
        };
    }

    protected function windowLabel(CarbonImmutable $start, CarbonImmutable $end): string
    {
        return match ($this->activeView) {
            'day'   => $start->format('l, d M Y'),
            'month' => $start->format('F Y'),
            default => $start->format('M') === $end->format('M')
                ? $start->format('d') . ' – ' . $end->format('d M Y')
                : $start->format('d M') . ' – ' . $end->format('d M Y'),
        };
    }

    protected function getViewData(): array
    {
        [$start, $end] = $this->windowBounds();
        $today = CarbonImmutable::today();

        $filter = $this->statusFilter && isset(Builder::FILTERS[$this->statusFilter]) ? $this->statusFilter : null;
        $data = Builder::buildWindow(
            $start,
            $end,
            campus: $this->campus ?: null,
            filter: $filter,
            unit: $this->unit ?: null,
            search: trim((string) $this->search) ?: null,
        );

        // ----- Roster: one row per covering teacher (+ an "Unassigned" row first) -----
        $substitutes = Teacher::query()
            ->whereIn('id', collect($data['cells'])->pluck('substitute_id')->filter()->unique())
            ->get(['id', 'name', 'campus', 'subject'])
            ->keyBy('id');

        $rosterRows = [];
        foreach ($data['cells'] as $cell) {
            $key = $cell['substitute_id'] ?? 'unassigned';
            if (! isset($rosterRows[$key])) {
                $t = $cell['substitute_id'] ? $substitutes->get($cell['substitute_id']) : null;
                $rosterRows[$key] = [
                    'teacher_id'      => $cell['substitute_id'],
                    'teacher_name'    => $t?->name ?? $cell['substitute'] ?? 'Unassigned',
                    'teacher_campus'  => $t?->campus,
                    'teacher_subject' => $t?->subject,
                    'is_unassigned'   => $key === 'unassigned',
                    'lesson_count'    => 0,
                    'duty_count'      => 0,
                    'gap_count'       => 0,
                    'days'            => [],
                ];
            }
            $rosterRows[$key]['days'][$cell['date']][] = $cell;
            $rosterRows[$key][$cell['source'] === 'duty' ? 'duty_count' : 'lesson_count']++;
            $rosterRows[$key]['gap_count'] += (int) $cell['gap'];
        }

        usort($rosterRows, fn (array $a, array $b) => ($b['is_unassigned'] <=> $a['is_unassigned'])
            ?: ($b['gap_count'] <=> $a['gap_count'])
            ?: ($b['lesson_count'] + $b['duty_count'] <=> $a['lesson_count'] + $a['duty_count'])
            ?: strcmp((string) $a['teacher_name'], (string) $b['teacher_name']));

        // ----- Period grid: [date][period] => cells, duties in an "all-day" lane -----
        $grid = [];
        $allDay = [];
        foreach ($data['cells'] as $cell) {
            if ($cell['source'] === 'duty') {
                $allDay[$cell['date']][] = $cell;
            } else {
                $grid[$cell['date']][$cell['period']][] = $cell;
            }
        }

        // ----- Month calendar: weeks × Mon..Sat with per-day tallies -----
        $byDate = collect($data['cells'])->groupBy('date');
        $calendar = [];
        if ($this->activeView === 'month') {
            for ($w = $start->startOfWeek(); $w->lessThanOrEqualTo($end); $w = $w->addWeek()) {
                $week = [];
                for ($i = 0; $i < 6; $i++) {
                    $d = $w->addDays($i);
                    $cells = $byDate->get($d->toDateString(), collect());
                    $week[] = [
                        'date'      => $d->toDateString(),
                        'num'       => $d->day,
                        'in_month'  => $d->month === $start->month,
                        'is_today'  => $d->isSameDay($today),
                        'lessons'   => $cells->where('source', 'leave')->count(),
                        'gaps'      => $cells->where('gap', true)->count(),
                        'duties'    => $cells->where('source', 'duty')->count(),
                        'covering'  => $cells->pluck('substitute')->filter()->unique()->values()->all(),
                        'absent'    => $cells->pluck('original')->filter()->unique()->values()->all(),
                    ];
                }
                $calendar[] = $week;
            }
        }

        return [
            'view'              => $this->activeView,
            'layout'            => $this->display,
            'weekStart'         => $start->toDateString(),
            'weekEnd'           => $end->toDateString(),
            'windowLabel'       => $this->windowLabel($start, $end),
            'focusDate'         => $this->anchor()->toDateString(),
            'today'             => $today->toDateString(),
            'isSunday'          => $this->activeView === 'day' && $start->isSunday(),
            'campus'            => $this->campus,
            'unit'              => $this->unit,
            'search'            => $this->search,
            'statusFilter'      => $filter,
            'filters'           => Builder::FILTERS,
            'campuses'          => Builder::availableCampuses(),
            'units'             => Builder::availableUnits(),
            'data'              => $data,
            'summary'           => $data['summary'],
            'attention'         => $data['attention'],
            'periods'           => $data['periods'],
            'grid'              => $grid,
            'allDay'            => $allDay,
            'calendar'          => $calendar,
            'rosterRows'        => $rosterRows,
            'rosterDays'        => $data['days'],
            'leavesUrl'         => \App\Filament\Principal\Resources\TeacherLeaveResource::getUrl(),
        ];
    }
}
