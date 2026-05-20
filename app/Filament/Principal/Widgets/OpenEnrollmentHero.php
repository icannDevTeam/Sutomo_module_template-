<?php

namespace App\Filament\Principal\Widgets;

use App\Models\Application;
use App\Models\EnrollmentPeriod;
use Filament\Widgets\Widget;

class OpenEnrollmentHero extends Widget
{
    protected static string $view = 'filament.principal.widgets.open-enrollment-hero';
    protected static ?int $sort = -100;
    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $period = EnrollmentPeriod::query()->orderByDesc('opens_at')->first();
        $base = Application::query();
        if ($period) $base->where('enrollment_period_id', $period->id);

        return [
            'period'    => $period,
            'total'     => (clone $base)->count(),
            'incoming'  => (clone $base)->whereRaw("(payment_status != 'paid' OR payment_status IS NULL)")->count(),
            'accepted'  => (clone $base)->whereIn('status', \App\Filament\Principal\Pages\StudentOnboarding::acceptedStatuses())->count(),
        ];
    }
}
