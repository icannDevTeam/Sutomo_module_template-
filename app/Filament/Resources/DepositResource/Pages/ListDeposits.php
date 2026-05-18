<?php

namespace App\Filament\Resources\DepositResource\Pages;

use App\Filament\Resources\DepositResource;
use App\Support\CsvExporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeposits extends ListRecords
{
    protected static string $resource = DepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportAll')
                ->label('Export CSV')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray')
                ->action(fn () => CsvExporter::download(
                    $this->getFilteredTableQuery()->with('candidate')->get(),
                    DepositResource::csvColumns(),
                    CsvExporter::filename('deposits'),
                )),
            Actions\CreateAction::make(),
        ];
    }
}
