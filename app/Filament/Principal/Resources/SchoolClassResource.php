<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\SchoolClassResource\Pages;
use App\Models\SchoolClass;
use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SchoolClassResource extends Resource
{
    protected static ?string $model = SchoolClass::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationLabel = 'Classes';
    protected static ?string $navigationGroup = 'Academics';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->required(),
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\Select::make('campus')->options(['sd'=>'SD','smp'=>'SMP','sma'=>'SMA','int'=>'International'])->required(),
            Forms\Components\TextInput::make('grade')->required(),
            Forms\Components\Select::make('stream')->options(['ipa'=>'IPA','ips'=>'IPS','umum'=>'Umum']),
            Forms\Components\TextInput::make('room'),
            Forms\Components\Select::make('homeroom_teacher_id')->label('Homeroom Teacher')
                ->options(fn () => Teacher::pluck('name','id'))->searchable(),
            Forms\Components\TextInput::make('capacity')->numeric()->default(28),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable(),
                Tables\Columns\TextColumn::make('name')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('campus')->badge(),
                Tables\Columns\TextColumn::make('grade'),
                Tables\Columns\TextColumn::make('stream')->badge(),
                Tables\Columns\TextColumn::make('room'),
                Tables\Columns\TextColumn::make('homeroomTeacher.name')->label('Homeroom'),
                Tables\Columns\TextColumn::make('students_count')->counts('students')->label('Students'),
                Tables\Columns\TextColumn::make('capacity'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('campus')->options(['sd'=>'SD','smp'=>'SMP','sma'=>'SMA','int'=>'International']),
            ])
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSchoolClasses::route('/'),
            'create' => Pages\CreateSchoolClass::route('/create'),
            'edit'   => Pages\EditSchoolClass::route('/{record}/edit'),
        ];
    }
}
