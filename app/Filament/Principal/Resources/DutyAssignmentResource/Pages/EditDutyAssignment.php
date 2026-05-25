<?php

namespace App\Filament\Principal\Resources\DutyAssignmentResource\Pages;

use App\Filament\Principal\Resources\DutyAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDutyAssignment extends EditRecord
{
    protected static string $resource = DutyAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
