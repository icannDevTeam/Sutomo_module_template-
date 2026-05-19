<?php

namespace App\Filament\Principal\Pages;

use App\Filament\Principal\Widgets\AcademicMonitoring;
use App\Filament\Principal\Widgets\EnrollmentKpis;
use App\Filament\Principal\Widgets\EnrollmentTrendChart;
use App\Filament\Principal\Widgets\EscalationsList;
use App\Filament\Principal\Widgets\FinanceVisibility;
use App\Filament\Principal\Widgets\PrincipalHero;
use App\Filament\Principal\Widgets\TeacherOps;
use App\Filament\Principal\Widgets\UpcomingActivities;
use Filament\Pages\Dashboard as BaseDashboard;

class PrincipalDashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = -10;

    public function getTitle(): string
    {
        return 'Principal Dashboard';
    }

    public function getColumns(): int | string | array
    {
        return ['default' => 1, 'sm' => 2, 'lg' => 3];
    }

    public function getWidgets(): array
    {
        return [
            PrincipalHero::class,
            EnrollmentKpis::class,
            AcademicMonitoring::class,
            TeacherOps::class,
            EnrollmentTrendChart::class,
            FinanceVisibility::class,
            EscalationsList::class,
            UpcomingActivities::class,
        ];
    }
}
