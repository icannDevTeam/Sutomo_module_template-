<?php

namespace App\Filament\Widgets;

use App\Models\Teacher;
use Filament\Widgets\ChartWidget;

class TeachersByCampusChart extends ChartWidget
{
    protected static ?string $heading = 'Faculty by campus & status';
    protected static ?string $description = 'Active teacher headcount';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = ['default' => 'full', 'lg' => 2];
    protected static ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $campuses = ['sd' => 'SD', 'smp' => 'SMP', 'sma' => 'SMA', 'int' => 'International'];
        // Stacked dataset palette — covers legacy + PKWT tiers + leave/opl.
        $statuses = [
            'guru_sk'   => ['Guru SK',   '#10b981'],
            'permanent' => ['Permanent', '#059669'],
            'pkwt_3'    => ['PKWT-III',  '#0ea5e9'],
            'pkwt_2'    => ['PKWT-II',   '#3b82f6'],
            'pkwt_1'    => ['PKWT-I',    '#f59e0b'],
            'contract'  => ['Contract',  '#94a3b8'],
            'probation' => ['Probation', '#f97316'],
            'opl'       => ['OPL',       '#fb7185'],
            'leave'     => ['On Leave',  '#8b5cf6'],
        ];

        $datasets = [];
        foreach ($statuses as $key => [$label, $color]) {
            $row = [];
            foreach (array_keys($campuses) as $campus) {
                $row[] = Teacher::where('campus', $campus)->where('status', $key)->count();
            }
            $datasets[] = [
                'label'           => $label,
                'data'            => $row,
                'backgroundColor' => $color,
                'borderRadius'    => 6,
                'borderSkipped'   => false,
                'stack'           => 'faculty',
            ];
        }

        return [
            'datasets' => $datasets,
            'labels'   => array_values($campuses),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'bottom', 'labels' => ['boxWidth' => 12, 'padding' => 10, 'font' => ['size' => 11]]],
            ],
            'scales' => [
                'x' => ['stacked' => true, 'grid' => ['display' => false]],
                'y' => ['stacked' => true, 'beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
        ];
    }
}
