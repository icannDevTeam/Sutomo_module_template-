<?php
namespace App\Filament\Principal\Resources\EbookPackResource\Pages;

use App\Filament\Principal\Resources\EbookPackResource;
use App\Filament\Principal\Resources\EbookPlatformResource;
use App\Models\EbookPlatform;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListEbookPacks extends ListRecords
{
    protected static string $resource = EbookPackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('managePlatforms')
                ->label('Manage Platforms')
                ->icon('heroicon-o-globe-alt')
                ->color('gray')
                ->url(fn () => EbookPlatformResource::getUrl('index')),

            Actions\Action::make('addPlatform')
                ->label('Add Platform')
                ->icon('heroicon-o-plus-circle')
                ->color('gray')
                ->modalHeading('Add e-Book Platform')
                ->modalDescription('Quick-create a platform without leaving the catalog.')
                ->form([
                    Forms\Components\TextInput::make('name')->required()->placeholder('e.g. Quipper'),
                    Forms\Components\TextInput::make('base_url')->label('Base URL')->required()->url()
                        ->placeholder('https://learn.quipper.com'),
                    Forms\Components\TextInput::make('logo_url')->label('Logo URL')->url()
                        ->placeholder('https://… (optional)'),
                    Forms\Components\Toggle::make('is_active')->default(true),
                    Forms\Components\Textarea::make('notes')->rows(2)
                        ->placeholder('How to log in, support email, etc.'),
                ])
                ->action(function (array $data) {
                    EbookPlatform::create($data);
                    Notification::make()->title('Platform added')->success()->send();
                }),

            Actions\CreateAction::make(),
        ];
    }
}
