<?php
namespace App\Filament\Principal\Resources\EbookPackResource\Pages;
use App\Filament\Principal\Resources\EbookPackResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditEbookPack extends EditRecord {
    protected static string $resource = EbookPackResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
