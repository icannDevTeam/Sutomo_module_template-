<?php

namespace App\Filament\Pages;

use App\Filament\Resources\CandidateResource;
use App\Filament\Resources\DepositResource;
use App\Models\Candidate;
use App\Models\Deposit;
use App\Support\CsvExporter;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Pages\Page;

class Verifications extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Hiring';
    protected static ?int $navigationSort = 5;
    protected static string $view = 'filament.pages.verifications';
    protected static ?string $title = 'Verifications';

    public string $activeTab = 'deposits';

    public function setActiveTab(string $tab): void { $this->activeTab = $tab; }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('exportDeposits')
                    ->label('Deposits')
                    ->icon('heroicon-m-banknotes')
                    ->action(fn () => CsvExporter::download(
                        Deposit::with('candidate')->orderByDesc('paid_at')->get(),
                        DepositResource::csvColumns(),
                        CsvExporter::filename('verifications-deposits'),
                    )),
                Action::make('exportPsycho')
                    ->label('Psycho results')
                    ->icon('heroicon-m-puzzle-piece')
                    ->action(fn () => CsvExporter::download(
                        Candidate::whereNotNull('meta->psycho')->with('vacancy')->get(),
                        CandidateResource::csvColumns(),
                        CsvExporter::filename('verifications-psycho'),
                    )),
                Action::make('exportMedical')
                    ->label('Medical results')
                    ->icon('heroicon-m-heart')
                    ->action(fn () => CsvExporter::download(
                        Candidate::whereNotNull('meta->medical')->with('vacancy')->get(),
                        CandidateResource::csvColumns(),
                        CsvExporter::filename('verifications-medical'),
                    )),
                Action::make('exportYayasan')
                    ->label('Yayasan decisions')
                    ->icon('heroicon-m-building-library')
                    ->action(fn () => CsvExporter::download(
                        Candidate::whereNotNull('meta->yayasan')->with('vacancy')->get(),
                        CandidateResource::csvColumns(),
                        CsvExporter::filename('verifications-yayasan'),
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
            'deposits' => Deposit::with('candidate')->orderByDesc('paid_at')->get(),
            'psycho'   => Candidate::whereNotNull('meta->psycho')->with('vacancy')->get(),
            'medical'  => Candidate::whereNotNull('meta->medical')->with('vacancy')->get(),
            'yayasan'  => Candidate::whereNotNull('meta->yayasan')->with('vacancy')->get(),
        ];
    }
}
