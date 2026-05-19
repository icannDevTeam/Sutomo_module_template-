<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\StudentResource\Pages;
use App\Models\SchoolClass;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Students';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identity')->columns(2)->schema([
                Forms\Components\TextInput::make('nis')->label('NIS')->required(),
                Forms\Components\TextInput::make('name')->required()->columnSpanFull(),
                Forms\Components\Select::make('gender')->options(['M' => 'Male','F' => 'Female']),
                Forms\Components\DatePicker::make('dob')->label('Date of Birth'),
                Forms\Components\TextInput::make('religion'),
                Forms\Components\TextInput::make('ethnicity'),
                Forms\Components\TextInput::make('city'),
            ]),
            Forms\Components\Section::make('Placement')->columns(2)->schema([
                Forms\Components\Select::make('campus')->options(['sd'=>'SD','smp'=>'SMP','sma'=>'SMA','int'=>'International'])->required(),
                Forms\Components\TextInput::make('unit'),
                Forms\Components\TextInput::make('grade'),
                Forms\Components\Select::make('stream')->options(['ipa'=>'IPA','ips'=>'IPS','umum'=>'Umum'])->nullable(),
                Forms\Components\Select::make('school_class_id')->label('Class')
                    ->options(fn () => SchoolClass::pluck('name','id'))->searchable(),
                Forms\Components\Select::make('status')->options(Student::STATUSES)->default('active')->required(),
                Forms\Components\DatePicker::make('enrolled_at'),
            ]),
            Forms\Components\Section::make('Academic')->columns(3)->schema([
                Forms\Components\TextInput::make('attendance_rate')->numeric()->suffix('%'),
                Forms\Components\TextInput::make('gpa')->numeric()->step(0.01),
                Forms\Components\Select::make('fee_status')->options(['paid'=>'Paid','arrears'=>'Arrears','partial'=>'Partial'])->default('paid'),
            ]),
            Forms\Components\Section::make('Guardian')->columns(2)->schema([
                Forms\Components\TextInput::make('parent_name'),
                Forms\Components\TextInput::make('parent_phone')->tel(),
                Forms\Components\TextInput::make('parent_email')->email(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nis')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable()->weight('bold'),
                Tables\Columns\TextColumn::make('campus')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('grade')->badge(),
                Tables\Columns\TextColumn::make('schoolClass.name')->label('Class')->toggleable(),
                Tables\Columns\TextColumn::make('attendance_rate')->suffix('%')->sortable()
                    ->color(fn ($state) => $state >= 90 ? 'success' : ($state >= 80 ? 'warning' : 'danger')),
                Tables\Columns\TextColumn::make('gpa')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn ($state) => Student::STATUS_COLORS[$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('fee_status')->badge()
                    ->color(fn ($state) => $state === 'paid' ? 'success' : 'warning'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('campus')->options(['sd'=>'SD','smp'=>'SMP','sma'=>'SMA','int'=>'International']),
                Tables\Filters\SelectFilter::make('status')->options(Student::STATUSES),
                Tables\Filters\SelectFilter::make('grade')->options(fn () => Student::query()->distinct()->pluck('grade','grade')->filter()->toArray()),
                Tables\Filters\TernaryFilter::make('fee_status')->label('Arrears only')
                    ->placeholder('All')->trueLabel('Arrears')->falseLabel('Paid')
                    ->queries(true: fn ($q) => $q->where('fee_status','arrears'),
                              false: fn ($q) => $q->where('fee_status','paid'),
                              blank: fn ($q) => $q),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit'   => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
