<?php

namespace App\Filament\Principal\Widgets;

use App\Models\Announcement;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class AnnouncementsTicker extends Widget
{
    protected static string $view = 'filament.principal.widgets.announcements-ticker';
    protected static ?int $sort = -50;
    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $items = Announcement::query()
            ->whereIn('status', ['sent', 'scheduled'])
            ->orderByDesc('pinned')
            ->orderByDesc('sent_at')
            ->orderByDesc('updated_at')
            ->limit(7)
            ->get();

        $featured = $items->first();
        $more     = $items->skip(1)->take(3)->values();

        $weekAgo     = Carbon::now()->subDays(7);
        $newThisWeek = Announcement::query()
            ->whereIn('status', ['sent', 'scheduled'])
            ->where(function ($q) use ($weekAgo) {
                $q->where('sent_at', '>=', $weekAgo)
                  ->orWhere('scheduled_at', '>=', $weekAgo);
            })
            ->count();

        $scheduled = Announcement::query()->where('status', 'scheduled')->count();

        return [
            'featured'    => $featured,
            'more'        => $more,
            'total'       => $items->count(),
            'newThisWeek' => $newThisWeek,
            'scheduled'   => $scheduled,
        ];
    }
}
