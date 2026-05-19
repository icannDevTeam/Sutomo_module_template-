<?php
namespace App\Filament\Principal\Resources\StudentResource\Pages;
use App\Filament\Principal\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
