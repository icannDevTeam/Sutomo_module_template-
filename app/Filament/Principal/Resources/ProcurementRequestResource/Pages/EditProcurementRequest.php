<?php
namespace App\Filament\Principal\Resources\ProcurementRequestResource\Pages;
use App\Filament\Principal\Resources\ProcurementRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProcurementRequest extends EditRecord
{
    protected static string $resource = ProcurementRequestResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
