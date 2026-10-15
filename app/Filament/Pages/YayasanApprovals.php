<?php

namespace App\Filament\Pages;

use App\Filament\Resources\CandidateResource;
use App\Models\AuditLog;
use App\Models\Candidate;
use App\Support\CsvExporter;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class YayasanApprovals extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationGroup = 'Hiring';
    protected static ?int $navigationSort = 6;
    protected static string $view = 'filament.pages.yayasan-approvals';
    protected static ?string $title = 'Yayasan Approvals';
    protected static ?string $navigationLabel = 'Yayasan Approvals';
    protected static bool $shouldRegisterNavigation = false;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportPending')
                ->label('Export pending')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray')
                ->action(fn () => CsvExporter::download(
                    Candidate::where('stage', 'yayasan')->with('vacancy')->get(),
                    CandidateResource::csvColumns(),
                    CsvExporter::filename('yayasan-pending'),
                )),
        ];
    }

    /* RETIRED — decisions now live in the Yayasan portal at /yayasan/hiring-approvals */
    public function approve(int $id): void
    {
        Notification::make()
            ->title('Decisions are now recorded in the Yayasan portal')
            ->body('Visit /yayasan/hiring-approvals to approve or reject candidates.')
            ->warning()->send();
    }

    /* RETIRED — decisions now live in the Yayasan portal at /yayasan/hiring-approvals */
    public function reject(int $id): void
    {
        Notification::make()
            ->title('Decisions are now recorded in the Yayasan portal')
            ->body('Visit /yayasan/hiring-approvals to approve or reject candidates.')
            ->warning()->send();
    }

    public function getViewData(): array
    {
        return [
            'pending'  => Candidate::where('stage', 'yayasan')->with('vacancy')->get(),
            'approved' => Candidate::whereJsonContains('meta->yayasan->status', 'approved')->with('vacancy')->limit(50)->get(),
            'rejected' => Candidate::whereJsonContains('meta->yayasan->status', 'rejected')->with('vacancy')->limit(50)->get(),
        ];
    }
}
