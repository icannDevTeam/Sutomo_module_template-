<?php

namespace App\Filament\Principal\Resources\ApplicationResource\Pages;

use App\Filament\Principal\Resources\ApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewApplication extends ViewRecord
{
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('invoice')->label('Open Invoice')->icon('heroicon-o-document-currency-dollar')
                ->url(fn () => \App\Filament\Principal\Pages\ApplicationInvoice::getUrl(['record' => $this->record->id]))
                ->openUrlInNewTab(),
            Actions\EditAction::make(),
        ];
    }
}
