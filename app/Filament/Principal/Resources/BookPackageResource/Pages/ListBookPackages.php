<?php
namespace App\Filament\Principal\Resources\BookPackageResource\Pages;
use App\Filament\Principal\Resources\BookPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListBookPackages extends ListRecords {
    protected static string $resource = BookPackageResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
