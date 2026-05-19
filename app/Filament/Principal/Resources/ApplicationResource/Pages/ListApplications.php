<?php
namespace App\Filament\Principal\Resources\ApplicationResource\Pages;
use App\Filament\Principal\Resources\ApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
