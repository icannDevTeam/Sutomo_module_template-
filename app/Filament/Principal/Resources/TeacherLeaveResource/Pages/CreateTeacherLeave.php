<?php
namespace App\Filament\Principal\Resources\TeacherLeaveResource\Pages;
use App\Filament\Principal\Resources\TeacherLeaveResource;
use App\Support\SubstituteBroadcaster;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTeacherLeave extends CreateRecord
{
    protected static string $resource = TeacherLeaveResource::class;

    protected function afterCreate(): void
    {
        $record = $this->record;
        if ($record->status === 'pending'
            && ! $record->substitute_teacher_id
            && $record->auto_search_enabled) {
            $offers = SubstituteBroadcaster::startAutoSearch($record);
            if (! empty($offers)) {
                Notification::make()
                    ->title('Auto substitute search started')
                    ->body(count($offers) . ' teacher(s) invited to express interest.')
                    ->success()
                    ->send();
            }
        }
    }
}
