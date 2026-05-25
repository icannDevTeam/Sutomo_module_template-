<?php

namespace App\Filament\Principal\Resources\VoluntaryRequestResource\Pages;

use App\Filament\Principal\Resources\VoluntaryRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVoluntaryRequest extends EditRecord
{
    protected static string $resource = VoluntaryRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
