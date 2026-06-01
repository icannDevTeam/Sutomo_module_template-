<?php

namespace App\Filament\Principal\Resources\TeacherResource\Pages;

use App\Filament\Principal\Resources\TeacherResource;
use Filament\Resources\Pages\ListRecords;

class ListTeachers extends ListRecords
{
    protected static string $resource = TeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
