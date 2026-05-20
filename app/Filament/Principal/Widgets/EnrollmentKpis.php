<?php

namespace App\Filament\Principal\Widgets;

use App\Models\Application;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EnrollmentKpis extends BaseWidget
{
    protected static ?int $sort = -5;
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalApps = Application::count();
        $accepted  = Application::whereIn('status', Application::ONBOARDING_STATUSES)->count();
        $activated = Application::where('status', 'activated')->count();
        $waitlist  = Application::where('waitlisted', true)->count();
        $conv      = $totalApps > 0 ? round(($activated / $totalApps) * 100) : 0;

        return [
            Stat::make('Active Students', number_format(Student::where('status','active')->count()))
                ->description('Across all campuses')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),

            Stat::make('Applications', (string) $totalApps)
                ->description("$accepted accepted")
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Activated', (string) $activated)
                ->description("$conv% conversion")
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('info'),

            Stat::make('Waitlist', (string) $waitlist)
                ->description('Awaiting capacity')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
