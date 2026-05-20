<?php
namespace App\Filament\Principal\Resources\BookPackageResource\Pages;
use App\Filament\Principal\Resources\BookPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditBookPackage extends EditRecord {
    protected static string $resource = BookPackageResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
