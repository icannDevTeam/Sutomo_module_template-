<?php

namespace App\Filament\Widgets;

use App\Models\AuditLog;
use Filament\Widgets\Widget;

class RecentApprovals extends Widget
{
    protected static string $view = 'filament.widgets.recent-approvals';
    protected int|string|array $columnSpan = ['default' => 'full', 'lg' => 1];
    protected static ?int $sort = 7;

    public function getViewData(): array
    {
        $items = AuditLog::whereIn('action', ['approval.yayasan', 'stage.move', 'deposit.verified', 'override.approve'])
            ->orderByDesc('occurred_at')
            ->limit(8)
            ->get()
            ->map(function ($a) {
                $isReject = $a->to_value === 'rejected';
                return [
                    'icon'  => match (true) {
                        str_starts_with($a->action, 'approval') => $isReject ? '✖' : '✓',
                        str_starts_with($a->action, 'stage')    => '→',
                        str_starts_with($a->action, 'deposit')  => '💰',
                        default                                  => '★',
                    },
                    'color' => $isReject ? 'rose' : 'emerald',
                    'title' => str_replace(['.', '_'], ' ', $a->action) . ($a->to_value ? ' → ' . $a->to_value : ''),
                    'sub'   => $a->target . ' · ' . ($a->note ?: '—'),
                    'who'   => $a->user_name,
                    'when'  => $a->occurred_at?->diffForHumans(),
                ];
            });

        return ['items' => $items];
    }
}
