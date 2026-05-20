<?php
namespace App\Filament\Principal\Resources\EbookPlatformResource\Pages;
use App\Filament\Principal\Resources\EbookPlatformResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListEbookPlatforms extends ListRecords {
    protected static string $resource = EbookPlatformResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
