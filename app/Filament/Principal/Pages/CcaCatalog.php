<?php

namespace App\Filament\Principal\Pages;

use App\Models\SchoolEvent;
use Filament\Pages\Page;

class CcaCatalog extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?string $title = 'CCA / ECA Catalog';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.principal.pages.cca-catalog';

    public function getViewData(): array
    {
        return [
            'events' => SchoolEvent::whereIn('category', ['cca','competition','fieldtrip'])->orderBy('starts_at')->get(),
        ];
    }
}
