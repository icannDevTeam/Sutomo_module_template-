<?php

namespace App\Filament\Principal\Pages;

use App\Models\SupervisiEvaluation;
use App\Models\Teacher;
use Filament\Pages\Page;

class SupervisiGuru extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Teachers';
    protected static ?string $title = 'Supervisi Guru';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.principal.pages.supervisi-guru';

    public function getViewData(): array
    {
        return [
            'teachers'    => Teacher::count(),
            'scheduled'   => SupervisiEvaluation::where('status','scheduled')->count(),
            'completed'   => SupervisiEvaluation::where('status','completed')->count(),
            'training'    => SupervisiEvaluation::where('status','training')->count(),
            'records'     => SupervisiEvaluation::with('teacher')->latest('scheduled_at')->take(30)->get(),
        ];
    }
}
