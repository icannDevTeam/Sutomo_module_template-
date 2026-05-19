<?php
namespace App\Filament\Principal\Resources\SchoolEventResource\Pages;
use App\Filament\Principal\Resources\SchoolEventResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSchoolEvents extends ListRecords
{
    protected static string $resource = SchoolEventResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
