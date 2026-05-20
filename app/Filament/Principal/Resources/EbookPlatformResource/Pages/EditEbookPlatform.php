<?php
namespace App\Filament\Principal\Resources\EbookPlatformResource\Pages;
use App\Filament\Principal\Resources\EbookPlatformResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditEbookPlatform extends EditRecord {
    protected static string $resource = EbookPlatformResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
