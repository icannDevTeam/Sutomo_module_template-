<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\TeacherObservationResource\Pages;
use App\Models\Teacher;
use App\Models\TeacherObservation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TeacherObservationResource extends Resource
{
    protected static ?string $model = TeacherObservation::class;
    protected static ?string $navigationIcon = 'heroicon-o-eye';
    protected static ?string $navigationLabel = 'Teacher Observations';
    protected static ?string $navigationGroup = 'Teachers';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Lesson')->columns(2)->schema([
                Forms\Components\Select::make('teacher_id')->label('Teacher')
                    ->options(fn () => Teacher::orderBy('name')->pluck('name','id'))
                    ->searchable()->required(),
                Forms\Components\DateTimePicker::make('observed_at')->required()->default(now()),
                Forms\Components\TextInput::make('lesson_subject'),
                Forms\Components\TextInput::make('lesson_class_code')->label('Class'),
            ]),
            Forms\Components\Section::make('Dimensions (1–5)')->columns(4)->schema(
                collect(TeacherObservation::DIMENSIONS)->map(fn ($label, $key) =>
                    Forms\Components\Select::make("dimensions.$key")->label($label)
                        ->options([1=>'1',2=>'2',3=>'3',4=>'4',5=>'5'])
                )->all()
            ),
            Forms\Components\Section::make('Narrative')->schema([
                Forms\Components\Textarea::make('strengths')->rows(3),
                Forms\Components\Textarea::make('action_items')->rows(3),
                Forms\Components\DatePicker::make('follow_up_date'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('observed_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('observed_at')->dateTime('d M Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('teacher.name')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('lesson_subject')->placeholder('—'),
                Tables\Columns\TextColumn::make('lesson_class_code')->label('Class')->placeholder('—'),
                Tables\Columns\TextColumn::make('average_score')->label('Avg')->badge()
                    ->color(fn ($state) => $state === null ? 'gray' : ($state >= 4 ? 'success' : ($state >= 3 ? 'warning' : 'danger'))),
                Tables\Columns\TextColumn::make('observer.name')->label('Observer')->placeholder('—'),
                Tables\Columns\TextColumn::make('follow_up_date')->date()->placeholder('—')->toggleable(),
            ])
            ->filters([
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
            'index'  => Pages\ListTeacherObservations::route('/'),
            'create' => Pages\CreateTeacherObservation::route('/create'),
            'edit'   => Pages\EditTeacherObservation::route('/{record}/edit'),
        ];
    }
}
