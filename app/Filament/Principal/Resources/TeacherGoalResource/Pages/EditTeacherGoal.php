<?php

namespace App\Filament\Principal\Resources\TeacherGoalResource\Pages;

use App\Filament\Principal\Resources\TeacherGoalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTeacherGoal extends EditRecord
{
    protected static string $resource = TeacherGoalResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
