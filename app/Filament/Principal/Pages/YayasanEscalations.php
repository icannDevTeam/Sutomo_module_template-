<?php

namespace App\Filament\Principal\Pages;

use App\Models\BehaviorLog;
use App\Models\ProcurementRequest;
use Filament\Pages\Page;

class YayasanEscalations extends Page
{
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';
    protected static ?string $navigationGroup = 'Contract Management';
    protected static ?string $title = 'Yayasan Escalations';
    protected static ?int $navigationSort = 6;
    protected static string $view = 'filament.principal.pages.yayasan-escalations';

    public static function canAccess(): bool
    {
        return false;
    }

    public function mount(): void
    {
        abort(403);
    }

    public function getViewData(): array
    {
        return [
            'behavior'    => BehaviorLog::where('status', 'principal_action')->with('student')->latest()->get(),
            'procurement' => ProcurementRequest::where('status', 'yayasan_review')->latest()->get(),
        ];
    }
}
