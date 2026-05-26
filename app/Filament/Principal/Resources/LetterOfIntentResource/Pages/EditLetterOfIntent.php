<?php

namespace App\Filament\Principal\Resources\LetterOfIntentResource\Pages;

use App\Filament\Principal\Resources\LetterOfIntentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLetterOfIntent extends EditRecord
{
    protected static string $resource = LetterOfIntentResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
