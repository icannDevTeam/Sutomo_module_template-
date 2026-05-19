<?php

namespace App\Filament\Principal\Pages;

use App\Models\BehaviorLog;
use App\Models\ProcurementRequest;
use Filament\Pages\Page;

class YayasanEscalations extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';
    protected static ?string $navigationGroup = 'Approvals';
    protected static ?string $title = 'Yayasan Escalations';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.principal.pages.yayasan-escalations';

    public function getViewData(): array
    {
        return [
            'behavior'    => BehaviorLog::where('status', 'principal_action')->with('student')->latest()->get(),
            'procurement' => ProcurementRequest::where('status', 'yayasan_review')->latest()->get(),
        ];
    }
}
