<?php

namespace App\Filament\Widgets;

use App\Models\Candidate;
use App\Models\Interview;
use App\Models\Vacancy;
use Filament\Widgets\Widget;

class DashboardHero extends Widget
{
    protected static string $view = 'filament.widgets.dashboard-hero';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = -10;

    public function getViewData(): array
    {
        $hour = (int) now()->format('G');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

        return [
            'greeting'        => $greeting,
            'user'            => auth()->user()?->name ?? 'Admin',
            'role'            => 'HR Administrator',
            'today'           => now()->format('l, d F Y'),
            'time'            => now()->format('H:i'),
            'interviewsToday' => Interview::whereDate('scheduled_date', now()->toDateString())->count(),
            'newThisWeek'     => Candidate::where('applied_at', '>=', now()->subDays(7))->count(),
            'openVacancies'   => Vacancy::whereIn('status', ['open', 'closing'])->count(),
            'pending'         => Candidate::where('stage', 'yayasan')->count()
                                  + Candidate::where('stage', 'opl')->count(),
        ];
    }
}
