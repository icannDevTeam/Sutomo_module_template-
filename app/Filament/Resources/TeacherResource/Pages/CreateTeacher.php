<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use App\Models\SchoolClass;
use Filament\Resources\Pages\CreateRecord;

class CreateTeacher extends CreateRecord
{
    protected static string $resource = TeacherResource::class;

    protected array $homeroomClassIds = [];
    protected array $preferredSubIds = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->homeroomClassIds = array_map('intval', $data['homeroom_class_ids'] ?? []);
        $this->preferredSubIds  = array_map('intval', $data['preferred_substitute_ids'] ?? []);
        unset($data['homeroom_class_ids'], $data['preferred_substitute_ids']);
        return $data;
    }

    protected function afterCreate(): void
    {
        if (! empty($this->homeroomClassIds)) {
            SchoolClass::whereIn('id', $this->homeroomClassIds)
                ->update(['homeroom_teacher_id' => $this->record->id]);
        }

        $syncPayload = [];
        foreach ($this->preferredSubIds as $i => $subId) {
            $syncPayload[$subId] = ['rank' => min(3, $i + 1)];
        }
        if ($syncPayload) {
            $this->record->preferredSubstitutes()->sync($syncPayload);
        }

        // Auto-create "hired" employment event for new teachers
        if ($this->record->joined_at) {
            \App\Models\TeacherEmploymentEvent::create([
                'teacher_id' => $this->record->id,
                'event_date' => $this->record->joined_at,
                'event_type' => 'hired',
                'to_value'   => $this->record->title ?? 'teacher',
                'created_by' => auth()->id(),
            ]);
        }
    }
}

