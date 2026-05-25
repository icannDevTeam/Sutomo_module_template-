<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use App\Models\SchoolClass;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTeacher extends EditRecord
{
    protected static string $resource = TeacherResource::class;

    /** Stash multi-selects before save (they aren't real DB columns). */
    protected array $homeroomClassIds = [];
    protected array $preferredSubIds = [];

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->homeroomClassIds = array_map('intval', $data['homeroom_class_ids'] ?? []);
        $this->preferredSubIds  = array_map('intval', $data['preferred_substitute_ids'] ?? []);
        unset($data['homeroom_class_ids'], $data['preferred_substitute_ids']);
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

        // Sync preferred substitutes pivot (auto-rank by selection order: 1,2,3...)
        $syncPayload = [];
        foreach ($this->preferredSubIds as $i => $subId) {
            $syncPayload[$subId] = ['rank' => min(3, $i + 1)];
        }
        $this->record->preferredSubstitutes()->sync($syncPayload);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

