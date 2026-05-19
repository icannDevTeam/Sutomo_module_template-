<?php

namespace App\Filament\Principal\Widgets;

use App\Models\BehaviorLog;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AcademicMonitoring extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $attendance = (int) round(Student::avg('attendance_rate') ?? 0);
        $gpa        = round(Student::avg('gpa') ?? 0, 2);
        $openBeh    = BehaviorLog::whereNotIn('status', ['closed'])->count();
        $atRisk     = Student::where('attendance_rate', '<', 80)->count();

        return [
            Stat::make('Avg Attendance', $attendance.'%')
                ->color($attendance >= 90 ? 'success' : ($attendance >= 80 ? 'warning' : 'danger'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->description('All students'),

            Stat::make('Avg GPA', (string) $gpa)
                ->color('primary')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->description('Reporting period'),

            Stat::make('Open Behavior Cases', (string) $openBeh)
                ->color($openBeh > 5 ? 'danger' : 'warning')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->description('Awaiting closure'),

            Stat::make('At-Risk Students', (string) $atRisk)
                ->color('warning')
                ->descriptionIcon('heroicon-m-shield-exclamation')
                ->description('Attendance < 80%'),
        ];
    }
}
