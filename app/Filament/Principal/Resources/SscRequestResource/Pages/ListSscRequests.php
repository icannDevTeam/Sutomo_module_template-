<?php
namespace App\Filament\Principal\Resources\SscRequestResource\Pages;
use App\Filament\Principal\Resources\SscRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSscRequests extends ListRecords
{
    protected static string $resource = SscRequestResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
