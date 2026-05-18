<?php

namespace App\Filament\Widgets;

use App\Models\Vacancy;
use Filament\Widgets\ChartWidget;

class CandidatesByDepartmentChart extends ChartWidget
{
    protected static ?string $heading = 'Applicants by department';
    protected static ?string $description = 'Distribution across open positions';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = ['default' => 'full', 'lg' => 1];
    protected static ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $rows = Vacancy::query()
            ->selectRaw('dept, sum(applicants) as total')
            ->groupBy('dept')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $palette = ['#3b82f6','#10b981','#f59e0b','#f43f5e','#8b5cf6','#0ea5e9','#f97316','#14b8a6'];

        return [
            'datasets' => [[
                'data'             => $rows->pluck('total')->all(),
                'backgroundColor'  => array_slice($palette, 0, $rows->count()),
                'borderWidth'      => 0,
                'hoverOffset'      => 8,
            ]],
            'labels' => $rows->pluck('dept')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels'   => ['boxWidth' => 12, 'padding' => 10, 'font' => ['size' => 11]],
                ],
            ],
            'cutout' => '60%',
        ];
    }
}
