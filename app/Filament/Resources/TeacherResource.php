<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherResource\Pages;
use App\Models\Teacher;
use App\Support\CsvExporter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TeacherResource extends Resource
{
    protected static ?string $model = Teacher::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'People';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identity')->columns(2)->schema([
                Forms\Components\TextInput::make('code')->required(),
                Forms\Components\TextInput::make('employee_no'),
                Forms\Components\TextInput::make('name')->required()->columnSpanFull(),
                Forms\Components\Select::make('gender')->options(['M' => 'Male', 'F' => 'Female']),
                Forms\Components\DatePicker::make('dob')->label('Date of Birth'),
                Forms\Components\TextInput::make('email')->email(),
                Forms\Components\TextInput::make('phone')->tel(),
                Forms\Components\TextInput::make('city'),
            ]),
            Forms\Components\Section::make('Employment')->columns(2)->schema([
                Forms\Components\TextInput::make('subject'),
                Forms\Components\TextInput::make('dept')->label('Department'),
                Forms\Components\Select::make('campus')->options([
                    'sd' => 'SD', 'smp' => 'SMP', 'sma' => 'SMA', 'int' => 'International',
                ]),
                Forms\Components\Select::make('status')->options(Teacher::STATUSES)->required(),
                Forms\Components\Select::make('employment')->options([
                    'full-time' => 'Full-time', 'part-time' => 'Part-time',
                ])->default('full-time'),
                Forms\Components\DatePicker::make('joined_at')->label('Joined'),
                Forms\Components\TextInput::make('tenure'),
                Forms\Components\TextInput::make('contract'),
                Forms\Components\DatePicker::make('contract_end'),
            ]),
            Forms\Components\Section::make('Credentials')->columns(2)->schema([
                Forms\Components\Textarea::make('education')->columnSpanFull(),
                Forms\Components\TagsInput::make('certifications'),
                Forms\Components\TagsInput::make('languages'),
                Forms\Components\TextInput::make('rating')->numeric()->step(0.1)->suffix('/5'),
                Forms\Components\DatePicker::make('last_review'),
            ]),
            Forms\Components\Section::make('Recognition & Initiatives')->columns(2)->schema([
                Forms\Components\TagsInput::make('awards')->placeholder('Add award + year'),
                Forms\Components\TagsInput::make('initiatives')->placeholder('Add initiative'),
                Forms\Components\TextInput::make('children_quota')
                    ->label('Children tuition quota')
                    ->numeric()->minValue(0)->maxValue(10)
                    ->helperText('Max children allowed under teacher tuition benefit.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable()->fontFamily('mono'),
                Tables\Columns\TextColumn::make('name')->searchable()->weight('bold')
                    ->description(fn ($record) => $record->subject . ' · ' . strtoupper($record->campus ?? '')),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->formatStateUsing(fn ($state) => Teacher::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'permanent' => 'success', 'contract' => 'info', 'probation' => 'warning',
                        'opl' => 'warning', 'leave' => 'gray', 'alumni' => 'gray', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('employment')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('tenure'),
                Tables\Columns\TextColumn::make('rating')->numeric(1)->alignCenter()->icon('heroicon-m-star')->iconColor('warning'),
                Tables\Columns\TextColumn::make('joined_at')->date('M Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(Teacher::STATUSES),
                Tables\Filters\SelectFilter::make('campus')->options([
                    'sd' => 'SD', 'smp' => 'SMP', 'sma' => 'SMA', 'int' => 'International',
                ]),
            ])
            ->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([
                Tables\Actions\BulkAction::make('exportCsv')
                    ->label('Export selected (CSV)')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('gray')
                    ->deselectRecordsAfterCompletion()
                    ->action(fn ($records) => CsvExporter::download($records, static::csvColumns(), CsvExporter::filename('teachers'))),
                Tables\Actions\DeleteBulkAction::make(),
            ])])
            ->defaultSort('name');
    }

    public static function csvColumns(): array
    {
        return [
            'Code'           => 'code',
            'Employee No.'   => 'employee_no',
            'Name'           => 'name',
            'Gender'         => 'gender',
            'DOB'            => 'dob',
            'Email'          => 'email',
            'Phone'          => 'phone',
            'City'           => 'city',
            'Subject'        => 'subject',
            'Department'     => 'dept',
            'Campus'         => fn ($r) => strtoupper((string) $r->campus),
            'Status'         => fn ($r) => Teacher::STATUSES[$r->status] ?? $r->status,
            'Employment'     => 'employment',
            'Joined'         => 'joined_at',
            'Tenure'         => 'tenure',
            'Contract'       => 'contract',
            'Contract end'   => 'contract_end',
            'Education'      => 'education',
            'Certifications' => 'certifications',
            'Languages'      => 'languages',
            'Rating'         => 'rating',
            'Last review'    => 'last_review',
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTeachers::route('/'),
            'create' => Pages\CreateTeacher::route('/create'),
            'view'   => Pages\ViewTeacher::route('/{record}'),
            'edit'   => Pages\EditTeacher::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['code', 'employee_no', 'name', 'email', 'subject', 'dept'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Subject' => $record->subject ?? '—',
            'Campus'  => strtoupper((string) $record->campus),
            'Status'  => Teacher::STATUSES[$record->status] ?? $record->status,
        ];
    }
}
