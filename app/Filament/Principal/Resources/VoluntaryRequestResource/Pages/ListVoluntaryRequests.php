<?php

namespace App\Filament\Principal\Resources\VoluntaryRequestResource\Pages;

use App\Filament\Principal\Resources\VoluntaryRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVoluntaryRequests extends ListRecords
{
    protected static string $resource = VoluntaryRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
