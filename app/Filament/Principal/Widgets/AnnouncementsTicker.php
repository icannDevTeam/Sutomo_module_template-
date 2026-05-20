<?php

namespace App\Filament\Principal\Widgets;

use App\Models\Announcement;
use Filament\Widgets\Widget;

class AnnouncementsTicker extends Widget
{
    protected static string $view = 'filament.principal.widgets.announcements-ticker';
    protected static ?int $sort = -50;
    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        return [
            'items' => Announcement::query()
                ->whereIn('status', ['sent', 'scheduled'])
                ->orderByDesc('pinned')
                ->orderByDesc('sent_at')
                ->orderByDesc('updated_at')
                ->limit(6)
                ->get(),
        ];
    }
}
