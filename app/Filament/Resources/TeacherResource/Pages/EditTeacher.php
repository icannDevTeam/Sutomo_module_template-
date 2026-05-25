<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use App\Models\SchoolClass;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTeacher extends EditRecord
{
    protected static string $resource = TeacherResource::class;

    /** Stash the multi-select before save (it isn't a real DB column). */
    protected array $homeroomClassIds = [];

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->homeroomClassIds = array_map('intval', $data['homeroom_class_ids'] ?? []);
        unset($data['homeroom_class_ids']);
        return $data;
    }

    protected function afterSave(): void
    {
        $teacherId = $this->record->id;
        $chosen    = $this->homeroomClassIds;

        SchoolClass::where('homeroom_teacher_id', $teacherId)
            ->whereNotIn('id', $chosen ?: [0])
            ->update(['homeroom_teacher_id' => null]);

        if (! empty($chosen)) {
            SchoolClass::whereIn('id', $chosen)
                ->update(['homeroom_teacher_id' => $teacherId]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

