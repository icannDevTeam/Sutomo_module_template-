<?php

namespace App\Filament\Principal\Pages;

use App\Models\Student;
use Filament\Pages\Page;

class AcademicProgress extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationGroup = 'Academics';
    protected static ?string $title = 'Academic Progress & Report Cards';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.principal.pages.academic-progress';

    public function getViewData(): array
    {
        return [
            'top'    => Student::orderByDesc('gpa')->take(10)->get(),
            'atRisk' => Student::where('attendance_rate', '<', 80)->orWhere('gpa', '<', 65)->take(15)->get(),
        ];
    }
}
