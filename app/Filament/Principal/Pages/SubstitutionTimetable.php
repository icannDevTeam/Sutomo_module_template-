<?php

namespace App\Filament\Principal\Pages;

use App\Support\SubstitutionTimetable as Builder;
use Carbon\CarbonImmutable;
use Filament\Pages\Page;

class SubstitutionTimetable extends Page
{
    protected static ?string $navigationGroup = 'Planning';
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationLabel = 'Substitution Timetable';
    protected static ?string $title = 'Substitution Timetable';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.principal.pages.substitution-timetable';
    protected static ?string $slug = 'substitution-timetable';

    /** 'week' | 'today' | 'list' */
    public string $activeView = 'week';
    public ?string $weekStart = null;
    public ?string $campus = null;
    public ?string $statusFilter = null;

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

        return [
            'view'         => $this->activeView,
            'weekStart'    => $weekStart->toDateString(),
            'weekEnd'      => $weekStart->addDays(5)->toDateString(),
            'campus'       => $this->campus,
            'statusFilter' => $this->statusFilter,
            'campuses'     => Builder::availableCampuses(),
            'data'         => $data,
            'grid'         => $grid,
            'offGrid'      => $offGrid,
        ];
    }
}
