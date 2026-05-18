<?php

namespace App\Filament\Widgets;

use App\Models\Candidate;
use Filament\Widgets\Widget;

class HiringFunnel extends Widget
{
    protected static string $view = 'filament.widgets.hiring-funnel';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 5;

    public function getFunnel(): array
    {
        $stages = Candidate::STAGES;
        unset($stages['rejected'], $stages['active']);
        $counts = Candidate::selectRaw('stage, count(*) as c')->groupBy('stage')->pluck('c', 'stage');
        $max = max($counts->values()->all() ?: [1]);
        $colors = Candidate::STAGE_COLORS;
        $hex = [
            'applied' => '#64748b', 'screening' => '#0ea5e9', 'written' => '#0284c7',
            'interview' => '#f59e0b', 'psycho' => '#3b82f6', 'medical' => '#10b981',
            'yayasan' => '#f43f5e', 'opl' => '#f97316',
        ];
        $out = [];
        foreach ($stages as $key => $label) {
            $c = (int) ($counts[$key] ?? 0);
            $out[] = [
                'key'   => $key,
                'label' => $label,
                'count' => $c,
                'pct'   => $max ? round($c / $max * 100) : 0,
                'hex'   => $hex[$key] ?? '#3b82f6',
            ];
        }
        return $out;
    }
}
