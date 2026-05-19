<?php
namespace App\Filament\Principal\Resources\SchoolClassResource\Pages;
use App\Filament\Principal\Resources\SchoolClassResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSchoolClasses extends ListRecords
{
    protected static string $resource = SchoolClassResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
