<?php

namespace App\Filament\Principal\Resources\DutyAssignmentResource\Pages;

use App\Filament\Principal\Resources\DutyAssignmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDutyAssignment extends CreateRecord
{
    protected static string $resource = DutyAssignmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['assigned_by'] = $data['assigned_by'] ?? (auth()->user()?->name ?? 'Principal');
        return $data;
    }
}
