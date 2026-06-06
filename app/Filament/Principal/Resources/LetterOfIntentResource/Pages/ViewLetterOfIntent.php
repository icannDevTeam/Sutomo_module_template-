<?php

namespace App\Filament\Principal\Resources\LetterOfIntentResource\Pages;

use App\Filament\Principal\Pages\LetterOfIntentFollowUp;
use App\Filament\Principal\Resources\LetterOfIntentResource;
use App\Models\LetterOfIntent;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

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

            Action::make('view_contract')
                ->label('View Contract')
                ->icon('heroicon-o-document-text')
                ->visible(fn () => ! is_null($this->record->yayasan_contract_path))
                ->url(fn () => Storage::disk('public')->url((string) $this->record->yayasan_contract_path))
                ->openUrlInNewTab(),

            Action::make('upload_yayasan_contract')
                ->label('Upload Yayasan Contract')
                ->icon('heroicon-o-cloud-arrow-up')
                ->color('warning')
                ->visible(fn () => ! is_null($this->record->submitted_to_yayasan_at) && is_null($this->record->yayasan_contract_uploaded_at))
                ->form([
                    FileUpload::make('file')
                        ->label('Contract PDF')
                        ->disk('public')
                        ->directory('yayasan-contracts')
                        ->acceptedFileTypes(['application/pdf'])
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $path = (string) ($data['file'] ?? '');
                    $this->record->update([
                        'yayasan_contract_path'        => $path,
                        'yayasan_contract_uploaded_at' => now(),
                        'yayasan_review_status'        => 'uploaded',
                    ]);
                    Notification::make()->title('Yayasan contract uploaded. Ready for principal review.')->success()->send();
                }),

            Action::make('print_contract')
                ->label('Print Contract')
                ->icon('heroicon-o-printer')
                ->visible(fn () => ! is_null($this->record->yayasan_contract_path))
                ->url(fn () => Storage::disk('public')->url((string) $this->record->yayasan_contract_path))
                ->openUrlInNewTab(),

            Action::make('download_contract')
                ->label('Download Contract')
                ->icon('heroicon-o-arrow-down-tray')
                ->visible(fn () => ! is_null($this->record->yayasan_contract_path))
                ->url(fn () => Storage::disk('public')->url((string) $this->record->yayasan_contract_path))
                ->openUrlInNewTab(),

            Action::make('accept_contract')
                ->label('Accept Contract')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => ! is_null($this->record->yayasan_contract_uploaded_at) && is_null($this->record->agreement_signed_at))
                ->action(function (): void {
                    $this->record->update([
                        'yayasan_review_status' => 'accepted',
                        'yayasan_review_notes'  => $this->record->yayasan_review_notes ?: 'Looks correct. Proceed to Agreement Letter signing.',
                        'yayasan_reviewed_by'   => auth()->id(),
                        'yayasan_reviewed_at'   => now(),
                    ]);
                    Notification::make()->title('Contract accepted. Agreement Letter can now be signed.')->success()->send();
                }),

            Action::make('resubmit_contract')
                ->label('Resubmit to Yayasan')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->visible(fn () => ! is_null($this->record->yayasan_contract_uploaded_at) && is_null($this->record->agreement_signed_at))
                ->form([
                    Textarea::make('note')
                        ->label('Issue note for Yayasan')
                        ->required()
                        ->maxLength(2000),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'yayasan_review_status'  => 'needs_revision',
                        'yayasan_resubmit_notes' => trim((string) ($data['note'] ?? '')),
                        'yayasan_reviewed_by'    => auth()->id(),
                        'yayasan_reviewed_at'    => now(),
                    ]);
                    Notification::make()->title('Contract sent back to Yayasan with revision note.')->warning()->send();
                }),
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

            Section::make('Contract Review')
                ->visible(fn ($record) => ! is_null($record->submitted_to_yayasan_at) || ! is_null($record->yayasan_contract_uploaded_at))
                ->columns(2)
                ->schema([
                    TextEntry::make('submitted_to_yayasan_at')->label('Submitted to Yayasan')->dateTime('d M Y H:i')->placeholder('—'),
                    TextEntry::make('yayasan_contract_uploaded_at')->label('Contract Uploaded')->dateTime('d M Y H:i')->placeholder('—'),
                    TextEntry::make('yayasan_review_status')
                        ->badge()
                        ->placeholder('—')
                        ->formatStateUsing(fn ($state) => LetterOfIntent::YAYASAN_REVIEW_STATUSES[$state] ?? '—')
                        ->color(fn ($state) => match ($state) {
                            'accepted'       => 'success',
                            'needs_revision' => 'danger',
                            'uploaded'       => 'warning',
                            default          => 'gray',
                        }),
                    TextEntry::make('yayasan_reviewed_at')->label('Last reviewed at')->dateTime('d M Y H:i')->placeholder('—'),
                    TextEntry::make('yayasan_review_notes')->label('Review note')->columnSpanFull()->placeholder('—'),
                    TextEntry::make('yayasan_resubmit_notes')->label('Resubmit note to Yayasan')->columnSpanFull()->placeholder('—'),
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
