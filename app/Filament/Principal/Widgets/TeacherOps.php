<?php

namespace App\Filament\Principal\Widgets;

use App\Models\SupervisiEvaluation;
use App\Models\Teacher;
use App\Models\TeacherLeave;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TeacherOps extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $active = Teacher::where('status', 'active')->count();
        $onLeave = TeacherLeave::where('status', 'approved')
            ->where('starts_at', '<=', now())->where('ends_at', '>=', now())->count();
        $pendingLeaves = TeacherLeave::where('status', 'pending')->count();
        $supScheduled  = SupervisiEvaluation::where('status', 'scheduled')->count();

        return [
            Stat::make('Active Teachers', (string) $active)->color('success'),
            Stat::make('On Leave Today', (string) $onLeave)->color('warning'),
            Stat::make('Pending Leave Reqs', (string) $pendingLeaves)->color('warning'),
            Stat::make('Supervisi Scheduled', (string) $supScheduled)->color('info'),
        ];
    }
}
