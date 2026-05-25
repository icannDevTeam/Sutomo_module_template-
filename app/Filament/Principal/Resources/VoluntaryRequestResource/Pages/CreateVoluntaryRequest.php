<?php

namespace App\Filament\Principal\Resources\VoluntaryRequestResource\Pages;

use App\Filament\Principal\Resources\VoluntaryRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVoluntaryRequest extends CreateRecord
{
    protected static string $resource = VoluntaryRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['submitted_at'] = $data['submitted_at'] ?? now();
        return $data;
    }
}
