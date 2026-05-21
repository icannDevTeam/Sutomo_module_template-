<?php

namespace App\Filament\Principal\Pages;

use App\Models\TimetableDefinition;
use App\Services\Timetable\HealthService;
use Filament\Pages\Page;

class TimetableHealth extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Planning';
    protected static ?string $title = 'Timetable Health';
    protected static ?string $navigationLabel = 'Health';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.principal.pages.timetable-health';

    public ?int $definitionId = null;
    public string $filterSeverity = 'all'; // all | error | warning | info
    public string $filterCategory = 'all';

    public function mount(): void
    {
        $def = TimetableDefinition::firstOrCreate(
            ['term' => 'TP 2025/2026', 'school_unit' => 'ALL'],
            ['status' => 'draft']
        );
        $this->definitionId = $def->id;
    }

    public function setSeverity(string $sev): void
    {
        $this->filterSeverity = $sev;
    }

    public function setCategory(string $cat): void
    {
        $this->filterCategory = $cat;
    }

    public function getViewData(): array
    {
        $report = app(HealthService::class)->report($this->definitionId);

        $issues = collect($report['issues']);
        if ($this->filterSeverity !== 'all') {
            $issues = $issues->where('severity', $this->filterSeverity);
        }
        if ($this->filterCategory !== 'all') {
            $issues = $issues->where('category', $this->filterCategory);
        }

        $categoryLabels = [
            'cell_conflict'      => 'Cell conflicts',
            'subject_deficit'    => 'Missing hours',
            'subject_excess'     => 'Excess hours',
            'over_cap'           => 'Over cap',
            'under_cap'          => 'Under-loaded',
            'unfilled_required'  => 'Unfilled required slots',
        ];

        return [
            'summary'        => $report['summary'],
            'byCategory'     => $report['byCategory'],
            'issues'         => $issues->values()->all(),
            'categoryLabels' => $categoryLabels,
            'filterSeverity' => $this->filterSeverity,
            'filterCategory' => $this->filterCategory,
        ];
    }
}
