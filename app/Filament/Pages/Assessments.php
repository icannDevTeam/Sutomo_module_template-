<?php

namespace App\Filament\Pages;

use App\Filament\Resources\CandidateResource;
use App\Models\Candidate;
use App\Models\Interview;
use App\Support\CsvExporter;
use Filament\Actions\Action;
use Filament\Pages\Page;

class Assessments extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Hiring';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.pages.assessments';
    protected static ?string $title = 'Assessments';

    public string $activeTab = 'tests';

    public function setActiveTab(string $tab): void { $this->activeTab = $tab; }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportScores')
                ->label('Export scores')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray')
                ->action(fn () => CsvExporter::download(
                    Candidate::whereNotNull('score_written')->with('vacancy')->orderByDesc('score_written')->get(),
                    CandidateResource::csvColumns(),
                    CsvExporter::filename('assessment-scores'),
                )),
        ];
    }

    public function getViewData(): array
    {
        $writtenCands = Candidate::whereNotNull('score_written')->with('vacancy')->orderByDesc('score_written')->get();
        $scheduled    = Candidate::where('stage', 'written')->count();
        $awaiting     = Candidate::where('stage', 'written')->whereNull('score_written')->count();
        $passRate     = $writtenCands->count() ? round($writtenCands->where('score_written', '>=', 70)->count() / $writtenCands->count() * 100) : 0;
        $avg          = $writtenCands->count() ? round($writtenCands->avg('score_written')) : 0;

        return [
            'writtenCands' => $writtenCands,
            'scheduled'    => $scheduled,
            'awaiting'     => $awaiting,
            'passRate'     => $passRate,
            'avg'          => $avg,
            'interviews'   => Interview::with('candidate')->orderByDesc('scheduled_date')->get(),
        ];
    }
}
