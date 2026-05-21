<?php

namespace App\Filament\Principal\Widgets;

use App\Models\UnitPlan;
use Filament\Widgets\Widget;

class ActivePlanning extends Widget
{
    protected static string $view = 'filament.principal.widgets.active-planning';
    protected static ?int $sort = -10; // Sits within Academic strip area
    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $units = UnitPlan::query()
            ->with('collaborators')
            ->whereIn('status', ['active', 'draft'])
            ->orderByDesc('last_activity_at')
            ->limit(4)
            ->get();

        return [
            'units'      => $units,
            'totalActive' => UnitPlan::where('status', 'active')->count(),
            'totalDraft'  => UnitPlan::where('status', 'draft')->count(),
            'liveNow'     => UnitPlan::query()
                ->whereHas('collaborators', fn ($q) => $q->where('online_now', true))
                ->count(),
        ];
    }
}
