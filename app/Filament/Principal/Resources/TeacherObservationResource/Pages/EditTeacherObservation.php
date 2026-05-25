<?php

namespace App\Filament\Principal\Resources\TeacherObservationResource\Pages;

use App\Filament\Principal\Resources\TeacherObservationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTeacherObservation extends EditRecord
{
    protected static string $resource = TeacherObservationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
