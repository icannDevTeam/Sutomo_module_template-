<?php

namespace App\Filament\Principal\Widgets;

use App\Models\EnrollmentPeriod;
use Filament\Widgets\Widget;

class EnrollmentStatusCard extends Widget
{
    protected static string $view = 'filament.principal.widgets.enrollment-status-card';
    protected static ?int $sort = -100;
    protected int|string|array $columnSpan = 1;

    protected function getViewData(): array
    {
        $period = EnrollmentPeriod::query()->orderByDesc('opens_at')->first();

        return [
            'period' => $period,
            'status' => $period?->status ?? 'closed',
            'name'   => $period?->name ?? 'No period configured',
        ];
    }
}
