<?php

namespace App\Filament\Resources\CandidateResource\Pages;

use App\Filament\Resources\CandidateResource;
use App\Support\CsvExporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCandidates extends ListRecords
{
    protected static string $resource = CandidateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportAll')
                ->label('Export CSV')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray')
                ->action(fn () => CsvExporter::download(
                    $this->getFilteredTableQuery()->with('vacancy')->get(),
                    CandidateResource::csvColumns(),
                    CsvExporter::filename('candidates'),
                )),
            Actions\CreateAction::make(),
        ];
    }
}
