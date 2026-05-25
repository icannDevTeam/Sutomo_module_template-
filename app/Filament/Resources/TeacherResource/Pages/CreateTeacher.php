<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use App\Models\SchoolClass;
use Filament\Resources\Pages\CreateRecord;

class CreateTeacher extends CreateRecord
{
    protected static string $resource = TeacherResource::class;

    protected array $homeroomClassIds = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->homeroomClassIds = array_map('intval', $data['homeroom_class_ids'] ?? []);
        unset($data['homeroom_class_ids']);
        return $data;
    }

    protected function afterCreate(): void
    {
        if (empty($this->homeroomClassIds)) {
            return;
        }
        SchoolClass::whereIn('id', $this->homeroomClassIds)
            ->update(['homeroom_teacher_id' => $this->record->id]);
    }
}

