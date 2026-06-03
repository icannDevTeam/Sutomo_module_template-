<?php

namespace App\Filament\Principal\Resources\TeacherObservationResource\Widgets;

use App\Models\SupervisiEvaluation;
use App\Models\Teacher;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SupervisionStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Teachers', Teacher::count())
                ->color('gray')
                ->icon('heroicon-o-identification'),
            Stat::make('Scheduled', SupervisiEvaluation::where('status', 'scheduled')->count())
                ->color('info')
                ->icon('heroicon-o-calendar'),
            Stat::make('Completed', SupervisiEvaluation::where('status', 'completed')->count())
                ->color('success')
                ->icon('heroicon-o-check-circle'),
            Stat::make('Training Needed', SupervisiEvaluation::where('status', 'training')->count())
                ->color('warning')
                ->icon('heroicon-o-academic-cap'),
        ];
    }
}
