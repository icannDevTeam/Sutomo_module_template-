<?php

namespace App\Filament\Principal\Resources\TeacherDocumentResource\Pages;

use App\Filament\Principal\Resources\TeacherDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTeacherDocuments extends ListRecords
{
    protected static string $resource = TeacherDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
