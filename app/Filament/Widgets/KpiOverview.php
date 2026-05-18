<?php

namespace App\Filament\Widgets;

use App\Models\Candidate;
use App\Models\Deposit;
use App\Models\Interview;
use App\Models\Vacancy;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class KpiOverview extends BaseWidget
{
    protected static ?int $sort = -9;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $pollingInterval = null;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Active vacancies', Vacancy::whereIn('status', ['open', 'closing'])->count())
                ->description(Vacancy::where('featured', true)->count() . ' featured')
                ->descriptionIcon('heroicon-m-star')
                ->color('primary')
                ->chart($this->weeklyCounts(fn ($s, $e) => Vacancy::whereBetween('posted_at', [$s, $e])->count())),

            Stat::make('In pipeline', Candidate::whereNotIn('stage', ['rejected', 'active'])->count())
                ->description(Candidate::where('applied_at', '>=', now()->subDays(7))->count() . ' new this week')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info')
                ->chart($this->weeklyCounts(fn ($s, $e) => Candidate::whereBetween('applied_at', [$s, $e])->count())),

            Stat::make(
                'Interviews this week',
                Interview::whereBetween('scheduled_date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            )
                ->description(Interview::where('status', 'scheduled')->count() . ' scheduled total')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning')
                ->chart($this->weeklyCounts(fn ($s, $e) => Interview::whereBetween('scheduled_date', [$s, $e])->count())),

            Stat::make(
                'Deposits collected',
                'Rp ' . number_format(Deposit::where('status', 'verified')->sum('amount') / 1_000_000, 1) . 'M',
            )
                ->description(Deposit::where('status', 'pending')->count() . ' pending verification')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart($this->weeklyCounts(
                    fn ($s, $e) => (int) (Deposit::where('status', 'verified')
                        ->whereBetween('paid_at', [$s, $e])->sum('amount') / 1_000_000),
                )),
        ];
    }

    /** @return int[] */
    private function weeklyCounts(\Closure $callback, int $weeks = 8): array
    {
        $out = [];
        for ($i = $weeks - 1; $i >= 0; $i--) {
            $start = Carbon::now()->startOfWeek()->subWeeks($i);
            $end   = (clone $start)->endOfWeek();
            $out[] = (int) $callback($start->toDateString(), $end->toDateString());
        }
        return $out;
    }
}
