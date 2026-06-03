<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\TeacherGoalResource\Pages;
use App\Models\Teacher;
use App\Models\TeacherGoal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TeacherGoalResource extends Resource
{
    protected static ?string $model = TeacherGoal::class;
    protected static ?string $navigationIcon = 'heroicon-o-flag';
    protected static ?string $navigationLabel = 'Teacher Goals';
    protected static ?string $navigationGroup = 'Supervision';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('teacher_id')->label('Teacher')
                ->options(fn () => Teacher::orderBy('name')->pluck('name','id'))
                ->searchable()->required(),
            Forms\Components\TextInput::make('title')->required()->columnSpanFull(),
            Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),
            Forms\Components\DatePicker::make('target_date'),
            Forms\Components\TextInput::make('academic_year')->placeholder('e.g. 2026/2027'),
            Forms\Components\Select::make('status')->options(TeacherGoal::STATUSES)->default('on_track')->required(),
            Forms\Components\TextInput::make('progress')->numeric()->minValue(0)->maxValue(100)->suffix('%')->default(0),
            Forms\Components\DatePicker::make('set_during_review_at'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('target_date')
            ->columns([
                Tables\Columns\TextColumn::make('teacher.name')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('title')->limit(60)->searchable(),
                Tables\Columns\TextColumn::make('academic_year')->placeholder('—'),
                Tables\Columns\TextColumn::make('target_date')->date()->placeholder('—'),
                Tables\Columns\TextColumn::make('progress')->suffix('%')->color(fn ($state) => $state >= 75 ? 'success' : ($state >= 40 ? 'warning' : 'danger')),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->formatStateUsing(fn ($state) => TeacherGoal::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => TeacherGoal::STATUS_COLORS[$state] ?? 'gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(TeacherGoal::STATUSES),
                Tables\Filters\SelectFilter::make('teacher_id')->options(fn () => Teacher::orderBy('name')->pluck('name','id'))->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTeacherGoals::route('/'),
            'create' => Pages\CreateTeacherGoal::route('/create'),
            'edit'   => Pages\EditTeacherGoal::route('/{record}/edit'),
        ];
    }
}
