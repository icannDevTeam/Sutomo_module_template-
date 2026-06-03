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
use Filament\Tables\Table;
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
            $n = LetterOfIntent::where('status', 'sent')->count();
            return $n > 0 ? (string) $n : null;
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
                    ->default('2026/2027'),
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
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('teacher.name')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('academic_year')->badge(),
                Tables\Columns\TextColumn::make('position')->placeholder('—'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => LetterOfIntent::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => LetterOfIntent::STATUS_COLORS[$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('sent_at')->date('d M Y')->placeholder('—'),
                Tables\Columns\TextColumn::make('signed_at')->date('d M Y')->placeholder('—'),
                Tables\Columns\TextColumn::make('deadline_at')->date('d M Y')->placeholder('—')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(LetterOfIntent::STATUSES),
                Tables\Filters\SelectFilter::make('teacher_id')
                    ->label('Teacher')
                    ->options(fn () => Teacher::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),
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
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
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
