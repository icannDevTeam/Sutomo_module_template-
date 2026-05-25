<?php

namespace App\Filament\Principal\Resources\TeacherObservationResource\Pages;

use App\Filament\Principal\Resources\TeacherObservationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTeacherObservation extends CreateRecord
{
    protected static string $resource = TeacherObservationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['observer_id'] = auth()->id();
        return $data;
    }
}
