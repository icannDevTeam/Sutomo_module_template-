<?php
namespace App\Filament\Principal\Resources\BehaviorLogResource\Pages;
use App\Filament\Principal\Resources\BehaviorLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBehaviorLog extends EditRecord
{
    protected static string $resource = BehaviorLogResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
