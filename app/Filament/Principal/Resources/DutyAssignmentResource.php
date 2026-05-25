<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\DutyAssignmentResource\Pages;
use App\Models\DutyAssignment;
use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DutyAssignmentResource extends Resource
{
    protected static ?string $model = DutyAssignment::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Duty Assignments';
    protected static ?string $navigationGroup = 'Approvals';
    protected static ?int $navigationSort = 5;

    public static function getNavigationBadge(): ?string
    {
        $c = DutyAssignment::where('status', 'pending')->count();
        return $c > 0 ? (string) $c : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('teacher_id')->label('Assigned to')
                ->options(fn () => Teacher::orderBy('name')->pluck('name', 'id'))
                ->searchable()->required(),
            Forms\Components\TextInput::make('title')->required()->maxLength(150),
            Forms\Components\TextInput::make('location')->maxLength(150),
            Forms\Components\DateTimePicker::make('starts_at')->required()->seconds(false),
            Forms\Components\DateTimePicker::make('ends_at')->seconds(false),
            Forms\Components\Select::make('status')->options(DutyAssignment::STATUSES)->default('pending')->required(),
            Forms\Components\Textarea::make('decline_reason')->rows(2)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('teacher.name')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('title')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('location')->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('starts_at')->dateTime('d M, H:i'),
                Tables\Columns\TextColumn::make('ends_at')->dateTime('d M, H:i')->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('assigned_by')->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->formatStateUsing(fn ($s) => DutyAssignment::STATUSES[$s] ?? $s)
                    ->color(fn ($s) => DutyAssignment::STATUS_COLORS[$s] ?? 'gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(DutyAssignment::STATUSES),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('markCompleted')->icon('heroicon-o-check-circle')->color('success')
                    ->label('Mark completed')
                    ->visible(fn ($record) => $record->status === 'accepted')
                    ->action(function ($record) {
                        $record->update(['status' => 'completed']);
                        Notification::make()->title('Duty marked completed')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDutyAssignments::route('/'),
            'create' => Pages\CreateDutyAssignment::route('/create'),
            'edit'   => Pages\EditDutyAssignment::route('/{record}/edit'),
        ];
    }
}
