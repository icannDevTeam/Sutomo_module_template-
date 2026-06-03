<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\LetterOfIntentResource\Pages;
use App\Models\LetterOfIntent;
use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LetterOfIntentResource extends Resource
{
    protected static ?string $model = LetterOfIntent::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'Letters of Intent';
    protected static ?string $navigationGroup = 'Contract Management';
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        try {
            // Items needing principal attention: awaiting signature + declined needing follow-up.
            $awaiting = LetterOfIntent::where('status', 'sent')->whereNull('archived_at')->count();
            $followUp = LetterOfIntent::query()->needsFollowUp()->notArchived()->count();
            $total    = $awaiting + $followUp;
            return $total > 0 ? (string) $total : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Letter')->columns(2)->schema([
                Forms\Components\Select::make('teacher_id')
                    ->label('Teacher')
                    ->options(fn () => Teacher::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('academic_year')
                    ->required()
                    ->default(self::currentAcademicYear()),
                Forms\Components\TextInput::make('position')
                    ->placeholder('e.g. Mathematics Teacher'),
                Forms\Components\DateTimePicker::make('deadline_at'),
                Forms\Components\Textarea::make('body')
                    ->rows(8)
                    ->columnSpanFull()
                    ->default("Dear {teacher_name},\n\nWe are pleased to extend an offer to continue your role as {position} at Sutomo School for academic year {academic_year}. Please review this letter and confirm your intent by signing below before {deadline}.\n\n— Principal"),
                Forms\Components\Textarea::make('notes')
                    ->rows(2)
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        $currentAy = self::currentAcademicYear();

        return $table
            ->defaultSort('academic_year', 'desc')
            ->groups([
                Group::make('academic_year')
                    ->label('Academic Year')
                    ->collapsible()
                    ->orderQueryUsing(fn (Builder $q) => $q->orderByDesc('academic_year')),
            ])
            ->defaultGroup('academic_year')
            ->columns([
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Teacher')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('academic_year')
                    ->badge()
                    ->color(fn ($state) => $state === $currentAy ? 'primary' : 'gray'),
                Tables\Columns\TextColumn::make('position')->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => LetterOfIntent::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => LetterOfIntent::STATUS_COLORS[$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('follow_up_status')
                    ->label('Follow-up')
                    ->badge()
                    ->placeholder('—')
                    ->formatStateUsing(fn ($state) => LetterOfIntent::FOLLOW_UP_STATUSES[$state] ?? '—')
                    ->color(fn ($state) => match ($state) {
                        'meeting_logged'      => 'info',
                        'resignation_pending' => 'warning',
                        'ready_for_hr'        => 'success',
                        'closed'              => 'gray',
                        default               => 'gray',
                    })
                    ->toggleable(),
                Tables\Columns\IconColumn::make('resignation_letter_path')
                    ->label('Resignation')
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('hr_handoff_status')
                    ->label('HR')
                    ->badge()
                    ->placeholder('—')
                    ->formatStateUsing(fn ($state) => LetterOfIntent::HR_HANDOFF_STATUSES[$state] ?? '—')
                    ->color(fn ($state) => match ($state) {
                        'ready'    => 'warning',
                        'uploaded' => 'success',
                        default    => 'gray',
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sent_at')->date('d M Y')->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('signed_at')->date('d M Y')->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('deadline_at')->date('d M Y')->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('last_reminder_at')
                    ->label('Last reminder')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('archived_at')
                    ->label('Archived')
                    ->boolean()
                    ->trueIcon('heroicon-o-archive-box')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('gray')
                    ->falseColor('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(LetterOfIntent::STATUSES),
                Tables\Filters\SelectFilter::make('teacher_id')
                    ->label('Teacher')
                    ->options(fn () => Teacher::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('academic_year')
                    ->options(fn () => LetterOfIntent::query()
                        ->select('academic_year')->distinct()->orderByDesc('academic_year')
                        ->pluck('academic_year', 'academic_year')->toArray()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (Model $record) => $record->status === 'draft'),
                Tables\Actions\Action::make('send')
                    ->label('Send')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Model $record) => $record->status === 'draft')
                    ->action(function (Model $record) {
                        $record->forceFill([
                            'status'       => 'sent',
                            'sent_at'      => now(),
                            'principal_id' => auth()->id(),
                        ])->save();
                        Notification::make()->title('Letter sent')->success()->send();
                    }),
                Tables\Actions\Action::make('open_sign')
                    ->label('Open Sign Page')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->visible(fn (Model $record) => $record->status === 'sent')
                    ->url(fn (Model $record) => \App\Filament\Principal\Pages\SignLetterOfIntent::getUrl(['record' => $record->id], panel: 'principal'), shouldOpenInNewTab: true),
                Tables\Actions\Action::make('follow_up')
                    ->label('Follow-up')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('warning')
                    ->visible(fn (Model $record) => $record->status === 'declined' && $record->follow_up_status !== 'closed')
                    ->url(fn (Model $record) => \App\Filament\Principal\Pages\LetterOfIntentFollowUp::getUrl(['record' => $record->id], panel: 'principal')),
                Tables\Actions\Action::make('archive')
                    ->label('Archive')
                    ->icon('heroicon-o-archive-box')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (Model $record) => is_null($record->archived_at) && $record->academic_year !== $currentAy)
                    ->action(function (Model $record) {
                        $record->update(['archived_at' => now()]);
                        Notification::make()->title('Archived')->success()->send();
                    }),
                Tables\Actions\Action::make('unarchive')
                    ->label('Unarchive')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('gray')
                    ->visible(fn (Model $record) => ! is_null($record->archived_at))
                    ->action(function (Model $record) {
                        $record->update(['archived_at' => null]);
                        Notification::make()->title('Unarchived')->success()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('archive_selected')
                    ->label('Archive selected')
                    ->icon('heroicon-o-archive-box')
                    ->requiresConfirmation()
                    ->action(fn ($records) => $records->each->update(['archived_at' => now()])),
                Tables\Actions\BulkAction::make('unarchive_selected')
                    ->label('Unarchive selected')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->action(fn ($records) => $records->each->update(['archived_at' => null])),
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function currentAcademicYear(): string
    {
        $now   = now();
        $start = $now->month >= 7 ? $now->year : $now->year - 1;
        return $start . '/' . ($start + 1);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLettersOfIntent::route('/'),
            'create' => Pages\CreateLetterOfIntent::route('/create'),
            'view'   => Pages\ViewLetterOfIntent::route('/{record}'),
            'edit'   => Pages\EditLetterOfIntent::route('/{record}/edit'),
        ];
    }
}
