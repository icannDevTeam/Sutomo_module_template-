<?php

namespace App\Filament\Principal\Resources\DutyAssignmentResource\Pages;

use App\Filament\Principal\Resources\DutyAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDutyAssignments extends ListRecords
{
    protected static string $resource = DutyAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
