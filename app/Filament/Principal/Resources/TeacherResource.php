<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\TeacherResource\Pages;
use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TeacherResource extends Resource
{
    protected static ?string $model = Teacher::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Teachers';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Master Teacher Database (Book Induk)';
    protected static ?string $modelLabel = 'Teacher';
    protected static ?string $pluralModelLabel = 'Teachers';
    protected static ?string $slug = 'teachers';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identity')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')->required()->maxLength(150),
                    Forms\Components\TextInput::make('code')->label('Employee code')->maxLength(50),
                    Forms\Components\TextInput::make('employee_no')->label('Employee no.')->maxLength(50),
                    Forms\Components\Select::make('gender')->options([
                        'male' => 'Male', 'female' => 'Female',
                    ]),
                    Forms\Components\DatePicker::make('dob')->label('Date of birth'),
                    Forms\Components\TextInput::make('email')->email()->maxLength(150),
                    Forms\Components\TextInput::make('phone')->tel()->maxLength(50),
                    Forms\Components\TextInput::make('city')->maxLength(100),
                ]),

            Forms\Components\Section::make('Assignment')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('subject')->maxLength(100),
                    Forms\Components\TextInput::make('dept')->label('Department')->maxLength(100),
                    Forms\Components\TextInput::make('campus')->maxLength(100),
                    Forms\Components\Select::make('status')
                        ->options(Teacher::STATUSES ?? [
                            'permanent' => 'Permanent', 'contract' => 'Contract',
                            'probation' => 'Probation', 'opl' => 'OPL',
                            'leave' => 'On leave', 'alumni' => 'Alumni',
                        ]),
                    Forms\Components\TextInput::make('employment')->maxLength(100),
                    Forms\Components\DatePicker::make('joined_at'),
                    Forms\Components\TextInput::make('contract')->maxLength(100),
                    Forms\Components\DatePicker::make('contract_end'),
                ]),

            Forms\Components\Section::make('Background')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('education')->maxLength(255),
                    Forms\Components\TextInput::make('tenure')->maxLength(100),
                    Forms\Components\DatePicker::make('last_review'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()->sortable()->weight('bold'),
                Tables\Columns\TextColumn::make('subject')
                    ->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('dept')
                    ->label('Dept')->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('campus')
                    ->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'permanent' => 'success',
                        'contract'  => 'info',
                        'probation' => 'warning',
                        'opl'       => 'gray',
                        'leave'     => 'warning',
                        'alumni'    => 'danger',
                        default     => 'gray',
                    }),
                Tables\Columns\TextColumn::make('contract_end')
                    ->label('Contract ends')->date()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('last_review')
                    ->label('Last review')->date()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('campus')
                    ->options(fn () => Teacher::query()->whereNotNull('campus')
                        ->distinct()->orderBy('campus')->pluck('campus', 'campus')->all()),
                Tables\Filters\SelectFilter::make('dept')
                    ->label('Department')
                    ->options(fn () => Teacher::query()->whereNotNull('dept')
                        ->distinct()->orderBy('dept')->pluck('dept', 'dept')->all()),
                Tables\Filters\SelectFilter::make('status')
                    ->options(Teacher::STATUSES ?? []),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Open profile'),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeachers::route('/'),
            'view'  => Pages\ViewTeacher::route('/{record}'),
            'edit'  => Pages\EditTeacher::route('/{record}/edit'),
        ];
    }
}
