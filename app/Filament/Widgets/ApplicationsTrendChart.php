<?php

namespace App\Filament\Widgets;

use App\Models\Candidate;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ApplicationsTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Applications — last 8 weeks';
    protected static ?string $description = 'New candidates received per week';
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = ['default' => 'full', 'lg' => 2];
    protected static ?string $maxHeight = '260px';

    public ?string $filter = '8w';

    protected function getFilters(): ?array
    {
        return [
            '4w'  => 'Last 4 weeks',
            '8w'  => 'Last 8 weeks',
            '12w' => 'Last 12 weeks',
        ];
    }

    protected function getData(): array
    {
        $weeks = (int) str_replace('w', '', $this->filter ?? '8w');
        $labels = [];
        $values = [];

        for ($i = $weeks - 1; $i >= 0; $i--) {
            $start = Carbon::now()->startOfWeek()->subWeeks($i);
            $end   = (clone $start)->endOfWeek();
            $labels[] = $start->format('d M');
            $values[] = Candidate::whereBetween('applied_at', [$start->toDateString(), $end->toDateString()])->count();
        }

        return [
            'datasets' => [
                [
                    'label'                => 'New applications',
                    'data'                 => $values,
                    'borderColor'          => '#3b82f6',
                    'backgroundColor'      => 'rgba(59, 130, 246, 0.15)',
                    'fill'                 => true,
                    'tension'              => 0.4,
                    'borderWidth'          => 2,
                    'pointBackgroundColor' => '#3b82f6',
                    'pointRadius'          => 3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
        ];
    }
}
