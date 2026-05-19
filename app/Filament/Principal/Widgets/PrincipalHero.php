<?php

namespace App\Filament\Principal\Widgets;

use App\Models\Application;
use App\Models\Student;
use Filament\Widgets\Widget;

class PrincipalHero extends Widget
{
    protected static string $view = 'filament.principal.widgets.hero';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = -10;

    public function getViewData(): array
    {
        $hour = (int) now()->format('G');
        $greeting = $hour < 12 ? 'Selamat pagi' : ($hour < 18 ? 'Selamat siang' : 'Selamat malam');

        return [
            'greeting'       => $greeting,
            'user'           => auth()->user()?->name ?? 'Principal',
            'today'          => now()->format('l, d F Y'),
            'time'           => now()->format('H:i'),
            'students'       => Student::where('status', 'active')->count(),
            'pendingApps'    => Application::whereIn('status', ['submitted', 'payment_confirmed', 'exam_scheduled'])->count(),
            'observation'    => Student::where('status', 'observation')->count(),
        ];
    }
}
