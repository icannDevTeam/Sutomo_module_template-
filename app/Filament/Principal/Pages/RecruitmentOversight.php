<?php

namespace App\Filament\Principal\Pages;

use App\Models\Candidate;
use App\Models\Vacancy;
use Filament\Pages\Page;

class RecruitmentOversight extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-eye';
    protected static ?string $navigationGroup = 'Teachers';
    protected static ?string $title = 'Recruitment Oversight';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.principal.pages.recruitment-oversight';

    public function getViewData(): array
    {
        return [
            'vacancies'  => Vacancy::whereIn('status', ['open','closing'])->withCount('candidates')->get(),
            'shortlist'  => Candidate::where('shortlisted', true)->take(30)->get(),
        ];
    }
}
