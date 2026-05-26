<?php

namespace App\Filament\Principal\Resources\LetterOfIntentResource\Pages;

use App\Filament\Principal\Resources\LetterOfIntentResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListLettersOfIntent extends ListRecords
{
    protected static string $resource = LetterOfIntentResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }

    public function getTabs(): array
    {
        return [
            'all'      => Tab::make('All'),
            'draft'    => Tab::make('Draft')->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft')),
            'sent'     => Tab::make('Sent')->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'sent')),
            'signed'   => Tab::make('Signed')->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'signed')),
            'declined' => Tab::make('Declined')->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'declined')),
        ];
    }
}
