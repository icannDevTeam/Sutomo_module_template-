<?php

namespace App\Filament\Principal\Pages;

use App\Models\Application;
use Filament\Pages\Page;

class PlacementExam extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';
    protected static ?string $navigationGroup = 'Enrollment';
    protected static ?string $title = 'Placement Exam';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.principal.pages.placement-exam';

    public function getViewData(): array
    {
        return [
            'scheduled' => Application::where('status', 'exam_scheduled')->orderBy('exam_date')->get(),
            'passed'    => Application::where('status', 'passed')->orderByDesc('placement_score')->take(15)->get(),
            'failed'    => Application::where('status', 'failed')->take(10)->get(),
            'avg'       => round((float) Application::whereNotNull('placement_score')->avg('placement_score'), 1),
        ];
    }
}
