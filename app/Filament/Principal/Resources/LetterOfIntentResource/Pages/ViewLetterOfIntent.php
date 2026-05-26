<?php

namespace App\Filament\Principal\Resources\LetterOfIntentResource\Pages;

use App\Filament\Principal\Resources\LetterOfIntentResource;
use App\Models\LetterOfIntent;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewLetterOfIntent extends ViewRecord
{
    protected static string $resource = LetterOfIntentResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Letter')->columns(2)->schema([
                TextEntry::make('teacher.name')->label('Teacher')->weight('bold'),
                TextEntry::make('academic_year')->badge(),
                TextEntry::make('position')->placeholder('—'),
                TextEntry::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => LetterOfIntent::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => LetterOfIntent::STATUS_COLORS[$state] ?? 'gray'),
                TextEntry::make('sent_at')->dateTime('d M Y H:i')->placeholder('—'),
                TextEntry::make('deadline_at')->dateTime('d M Y H:i')->placeholder('—'),
                TextEntry::make('principal.name')->label('Principal')->placeholder('—'),
                TextEntry::make('body')->columnSpanFull()->prose(),
                TextEntry::make('notes')->columnSpanFull()->placeholder('—'),
            ]),

            Section::make('Signature')
                ->visible(fn ($record) => $record->status === 'signed')
                ->columns(2)
                ->schema([
                    TextEntry::make('signature_text')->label('Signed by'),
                    TextEntry::make('signed_at')->dateTime('d M Y H:i'),
                    TextEntry::make('signature_ip')->label('IP Address')->placeholder('—'),
                ]),

            Section::make('Declined')
                ->visible(fn ($record) => $record->status === 'declined')
                ->schema([
                    TextEntry::make('decline_reason')->label('Reason')->placeholder('—'),
                ]),
        ]);
    }
}
