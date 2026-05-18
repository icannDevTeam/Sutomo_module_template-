<?php

namespace App\Filament\Widgets;

use App\Models\Deposit;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class DepositsCollectedChart extends ChartWidget
{
    protected static ?string $heading = 'Deposits collected (IDR)';
    protected static ?string $description = 'Verified deposit revenue over time';
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = ['default' => 'full', 'lg' => 1];
    protected static ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $labels = [];
        $verified = [];
        $pending = [];

        for ($i = 5; $i >= 0; $i--) {
            $start = Carbon::now()->startOfMonth()->subMonths($i);
            $end   = (clone $start)->endOfMonth();
            $labels[] = $start->format('M');
            $verified[] = (int) (Deposit::where('status', 'verified')
                ->whereBetween('paid_at', [$start->toDateString(), $end->toDateString()])
                ->sum('amount') / 1_000_000);
            $pending[] = (int) (Deposit::where('status', 'pending')
                ->whereBetween('due_date', [$start->toDateString(), $end->toDateString()])
                ->sum('amount') / 1_000_000);
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Verified (M IDR)',
                    'data'            => $verified,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.85)',
                    'borderRadius'    => 6,
                ],
                [
                    'label'           => 'Pending (M IDR)',
                    'data'            => $pending,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.85)',
                    'borderRadius'    => 6,
                ],
            ],
            'labels' => $labels,
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
                'x' => ['grid' => ['display' => false]],
                'y' => ['beginAtZero' => true],
            ],
        ];
    }
}
