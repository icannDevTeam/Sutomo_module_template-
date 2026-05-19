<?php
namespace App\Filament\Principal\Resources\BehaviorLogResource\Pages;
use App\Filament\Principal\Resources\BehaviorLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBehaviorLogs extends ListRecords
{
    protected static string $resource = BehaviorLogResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
