<?php

namespace App\Filament\Principal\Pages;

use App\Models\SchoolClass;
use Filament\Pages\Page;

class TimetableBuilder extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Academics';
    protected static ?string $title = 'Timetable & Class Distribution';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.principal.pages.timetable-builder';

    public function getViewData(): array
    {
        return [
            'classes' => SchoolClass::withCount('students')->orderBy('campus')->orderBy('grade')->get(),
        ];
    }
}
