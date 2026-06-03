<?php

namespace App\Filament\Principal\Resources\LetterOfIntentResource\Pages;

use App\Filament\Principal\Pages\LetterOfIntentFollowUp;
use App\Filament\Principal\Resources\LetterOfIntentResource;
use App\Models\LetterOfIntent;
use Filament\Actions\Action;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewLetterOfIntent extends ViewRecord
{
    protected static string $resource = LetterOfIntentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('open_follow_up')
                ->label('Open Follow-up')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('warning')
                ->visible(fn () => $this->record->status === 'declined')
                ->url(fn () => LetterOfIntentFollowUp::getUrl(['record' => $this->record->id])),
        ];
    }

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

            Section::make('Follow-up')
                ->visible(fn ($record) => $record->status === 'declined')
                ->columns(2)
                ->schema([
                    TextEntry::make('follow_up_status')
                        ->badge()
                        ->placeholder('—')
                        ->formatStateUsing(fn ($state) => LetterOfIntent::FOLLOW_UP_STATUSES[$state] ?? '—')
                        ->color(fn ($state) => match ($state) {
                            'meeting_logged'      => 'info',
                            'resignation_pending' => 'warning',
                            'ready_for_hr'        => 'success',
                            'closed'              => 'gray',
                            default               => 'gray',
                        }),
                    TextEntry::make('follow_up_outcome')
                        ->placeholder('—')
                        ->formatStateUsing(fn ($state) => LetterOfIntent::FOLLOW_UP_OUTCOMES[$state] ?? '—'),
                    TextEntry::make('meeting_notes')->columnSpanFull()->placeholder('—'),
                    TextEntry::make('actions_taken')->columnSpanFull()->placeholder('—'),
                    TextEntry::make('resignation_letter_path')
                        ->label('Resignation Letter')
                        ->placeholder('Not uploaded')
                        ->formatStateUsing(fn ($state) => $state ? '✓ Uploaded' : 'Not uploaded'),
                    TextEntry::make('resignation_letter_uploaded_at')
                        ->label('Uploaded at')
                        ->dateTime('d M Y H:i')
                        ->placeholder('—'),
                ]),

            Section::make('HR Handoff')
                ->visible(fn ($record) => ! is_null($record->hr_handoff_status))
                ->columns(2)
                ->schema([
                    TextEntry::make('hr_handoff_status')
                        ->badge()
                        ->formatStateUsing(fn ($state) => LetterOfIntent::HR_HANDOFF_STATUSES[$state] ?? '—')
                        ->color(fn ($state) => match ($state) {
                            'ready'    => 'warning',
                            'uploaded' => 'success',
                            default    => 'gray',
                        }),
                    TextEntry::make('hr_handoff_marked_at')->dateTime('d M Y H:i')->placeholder('—'),
                    TextEntry::make('hr_uploaded_at')->label('Buku Induk uploaded at')->dateTime('d M Y H:i')->placeholder('—'),
                ]),
        ]);
    }
}
