<?php

namespace App\Filament\Pages;

use App\Filament\Resources\CandidateResource;
use App\Filament\Resources\TeacherResource;
use App\Models\Candidate;
use App\Models\Teacher;
use App\Support\CsvExporter;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Pages\Page;

class Onboarding extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';
    protected static ?string $navigationGroup = 'Hiring';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.onboarding';
    protected static ?string $title = 'Onboarding';

    public string $activeTab = 'opl';

    public function setActiveTab(string $tab): void { $this->activeTab = $tab; }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('exportOPL')
                    ->label('OPL probation candidates')
                    ->action(fn () => CsvExporter::download(
                        Candidate::where('stage', 'opl')->with('vacancy')->get(),
                        CandidateResource::csvColumns(),
                        CsvExporter::filename('onboarding-opl'),
                    )),
                Action::make('exportProbation')
                    ->label('Probation teachers')
                    ->action(fn () => CsvExporter::download(
                        Teacher::where('status', 'probation')->get(),
                        TeacherResource::csvColumns(),
                        CsvExporter::filename('onboarding-probation'),
                    )),
                Action::make('exportContracts')
                    ->label('Contract teachers')
                    ->action(fn () => CsvExporter::download(
                        Teacher::where('status', 'contract')->get(),
                        TeacherResource::csvColumns(),
                        CsvExporter::filename('onboarding-contracts'),
                    )),
            ])
                ->label('Export')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray')
                ->button(),
        ];
    }

    public function getViewData(): array
    {
        return [
            'opl'       => Candidate::where('stage', 'opl')->with('vacancy')->get(),
            'probation' => Teacher::where('status', 'probation')->get(),
            'contracts' => Teacher::where('status', 'contract')->get(),
        ];
    }
}
