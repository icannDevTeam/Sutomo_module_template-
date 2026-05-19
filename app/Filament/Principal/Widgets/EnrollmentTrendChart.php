<?php

namespace App\Filament\Principal\Widgets;

use App\Models\Application;
use Filament\Widgets\ChartWidget;

class EnrollmentTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Applications — last 8 weeks';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 2;

    protected function getData(): array
    {
        $labels = [];
        $data   = [];
        for ($i = 7; $i >= 0; $i--) {
            $start = now()->subWeeks($i)->startOfWeek();
            $end   = now()->subWeeks($i)->endOfWeek();
            $labels[] = $start->format('d M');
            $data[]   = Application::whereBetween('applied_at', [$start, $end])->count();
        }
        return [
            'datasets' => [[
                'label' => 'Applications',
                'data' => $data,
                'borderColor' => '#4f46e5',
                'backgroundColor' => 'rgba(79,70,229,.15)',
                'fill' => true,
                'tension' => 0.35,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string { return 'line'; }
}
