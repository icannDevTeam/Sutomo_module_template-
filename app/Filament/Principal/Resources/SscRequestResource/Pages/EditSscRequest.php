<?php
namespace App\Filament\Principal\Resources\SscRequestResource\Pages;
use App\Filament\Principal\Resources\SscRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSscRequest extends EditRecord
{
    protected static string $resource = SscRequestResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
