<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\TeacherLeaveResource\Pages;
use App\Models\Teacher;
use App\Models\TeacherLeave;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TeacherLeaveResource extends Resource
{
    protected static ?string $model = TeacherLeave::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Teacher Leaves';
    protected static ?string $navigationGroup = 'Teachers';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('teacher_id')->label('Teacher')
                ->options(fn () => Teacher::orderBy('name')->pluck('name','id'))
                ->searchable()->required(),
            Forms\Components\Select::make('type')->options(TeacherLeave::TYPES)->required(),
            Forms\Components\DatePicker::make('starts_at')->required(),
            Forms\Components\DatePicker::make('ends_at')->required(),
            Forms\Components\Textarea::make('reason')->rows(2)->columnSpanFull(),
            Forms\Components\Select::make('status')->options(TeacherLeave::STATUSES)->default('pending')->required(),
            Forms\Components\Select::make('substitute_teacher_id')->label('Substitute')
                ->options(fn () => Teacher::orderBy('name')->pluck('name','id'))->searchable(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('teacher.name')->searchable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('starts_at')->date(),
                Tables\Columns\TextColumn::make('ends_at')->date(),
                Tables\Columns\TextColumn::make('substitute.name')->label('Substitute')->placeholder('—'),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn ($state) => TeacherLeave::STATUS_COLORS[$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('decided_by')->placeholder('—')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(TeacherLeave::STATUSES),
                Tables\Filters\SelectFilter::make('type')->options(TeacherLeave::TYPES),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')->icon('heroicon-o-check')->color('success')
                    ->visible(fn ($r) => $r->status === 'pending')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'approved',
                            'decided_by' => auth()->user()?->name ?? 'Principal',
                            'decided_at' => now(),
                        ]);
                        Notification::make()->title('Leave approved')->success()->send();
                    }),
                Tables\Actions\Action::make('reject')->icon('heroicon-o-x-mark')->color('danger')
                    ->visible(fn ($r) => $r->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'rejected',
                            'decided_by' => auth()->user()?->name ?? 'Principal',
                            'decided_at' => now(),
                        ]);
                        Notification::make()->title('Leave rejected')->warning()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTeacherLeaves::route('/'),
            'create' => Pages\CreateTeacherLeave::route('/create'),
            'edit'   => Pages\EditTeacherLeave::route('/{record}/edit'),
        ];
    }
}
