<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherResource\Pages;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\TeacherTag;
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
                Forms\Components\FileUpload::make('avatar_path')
                    ->label('Avatar')
                    ->image()
                    ->avatar()
                    ->disk('public')
                    ->directory('teachers/avatars')
                    ->imageEditor()
                    ->maxSize(4096)
                    ->acceptedFileTypes(['image/png','image/jpeg','image/webp'])
                    ->preserveFilenames(false)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('code')->required(),
                Forms\Components\TextInput::make('employee_no'),
                Forms\Components\TextInput::make('name')->required()->columnSpanFull(),
                Forms\Components\Select::make('title')
                    ->options(Teacher::TITLES)
                    ->default('teacher')
                    ->required(),
                Forms\Components\Select::make('gender')->options(['M' => 'Male', 'F' => 'Female']),
                Forms\Components\DatePicker::make('dob')->label('Date of Birth'),
                Forms\Components\TextInput::make('email')->email(),
                Forms\Components\TextInput::make('phone')->tel()
                    ->helperText('Indonesian numbers auto-normalize to +62…'),
                Forms\Components\TextInput::make('city'),
            ]),
            Forms\Components\Section::make('Emergency Contact')->columns(3)->schema([
                Forms\Components\TextInput::make('emergency_contact_name')->label('Name'),
                Forms\Components\TextInput::make('emergency_contact_relation')->label('Relation')
                    ->datalist(['Spouse','Parent','Sibling','Child','Friend','Other']),
                Forms\Components\TextInput::make('emergency_contact_phone')->label('Phone')->tel(),
            ]),
            Forms\Components\Section::make('Tags')
                ->description('Pin tags for quick filtering (e.g. "Trainer", "Bilingual", "Senior Mentor")')
                ->schema([
                    Forms\Components\Select::make('tags')
                        ->label('Teacher Tags')
                        ->relationship('tags', 'name')
                        ->multiple()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required()->unique('teacher_tags','name'),
                            Forms\Components\TextInput::make('color')->placeholder('#6366f1')->maxLength(16),
                        ]),
                ]),
            Forms\Components\Section::make('Homeroom Assignments')
                ->description('Classes where this teacher is the homeroom teacher')
                ->schema([
                    Forms\Components\Select::make('homeroom_class_ids')
                        ->label('Homeroom Classes')
                        ->multiple()
                        ->options(fn () => SchoolClass::orderBy('code')->pluck('code','id'))
                        ->searchable()
                        ->preload()
                        ->dehydrated()
                        ->afterStateHydrated(function ($component, ?Teacher $record) {
                            $component->state($record?->homeroomClasses()->pluck('school_classes.id')->all() ?? []);
                        }),
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
            ]),
            Forms\Components\Section::make('Recognition & Initiatives')->columns(2)->schema([
                Forms\Components\TagsInput::make('awards')->placeholder('Add award + year'),
                Forms\Components\TagsInput::make('initiatives')->placeholder('Add initiative'),
                Forms\Components\TextInput::make('children_quota')
                    ->label('Children tuition quota')
                    ->numeric()->minValue(0)->maxValue(10)
                    ->helperText('Max children allowed under teacher tuition benefit.'),
            ]),

            Forms\Components\Section::make('Mentorship')->columns(2)->schema([
                Forms\Components\Select::make('mentor_id')
                    ->label('Mentor')
                    ->relationship('mentor', 'name', fn ($query, $get, $record) => $query->when($record, fn ($q) => $q->whereKeyNot($record->id)))
                    ->searchable()->preload()
                    ->helperText('Senior teacher who mentors this person.'),
            ])->collapsed(),

            Forms\Components\Section::make('Formal Certifications')
                ->description('Official credentials (gov-approved or international). Use loose Tags for informal notes.')
                ->collapsed()
                ->schema([
                    Forms\Components\Repeater::make('formalCertifications')
                        ->relationship()
                        ->columns(3)
                        ->defaultItems(0)
                        ->collapsible()->collapsed()
                        ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                        ->schema([
                            Forms\Components\TextInput::make('name')->required()->columnSpan(2),
                            Forms\Components\Toggle::make('is_government_approved')->label('Govt Approved'),
                            Forms\Components\TextInput::make('issuer'),
                            Forms\Components\TextInput::make('country')->default('ID')->maxLength(2),
                            Forms\Components\TextInput::make('accreditation_no')->label('Accreditation #'),
                            Forms\Components\DatePicker::make('issued_at'),
                            Forms\Components\DatePicker::make('expires_at'),
                            Forms\Components\FileUpload::make('file_path')
                                ->disk('public')->directory('teachers/certs')
                                ->maxSize(8192)
                                ->acceptedFileTypes(['application/pdf','image/png','image/jpeg'])
                                ->columnSpan(3),
                            Forms\Components\Textarea::make('notes')->rows(2)->columnSpanFull(),
                        ]),
                ]),

            Forms\Components\Section::make('Clearances')
                ->description('Criminal record, child protection, medical, etc.')
                ->collapsed()
                ->schema([
                    Forms\Components\Repeater::make('clearances')
                        ->relationship()
                        ->columns(3)
                        ->defaultItems(0)
                        ->collapsible()->collapsed()
                        ->itemLabel(fn (array $state): ?string => isset($state['type']) ? (\App\Models\TeacherClearance::TYPES[$state['type']] ?? $state['type']) : null)
                        ->schema([
                            Forms\Components\Select::make('type')->options(\App\Models\TeacherClearance::TYPES)->required(),
                            Forms\Components\Select::make('status')->options(\App\Models\TeacherClearance::STATUSES)->default('valid'),
                            Forms\Components\TextInput::make('issuer'),
                            Forms\Components\DatePicker::make('issued_at'),
                            Forms\Components\DatePicker::make('expires_at'),
                            Forms\Components\FileUpload::make('file_path')
                                ->disk('public')->directory('teachers/clearances')
                                ->maxSize(8192)
                                ->columnSpan(3),
                            Forms\Components\Textarea::make('notes')->rows(2)->columnSpanFull(),
                        ]),
                ]),

            Forms\Components\Section::make('Preferred Substitutes')
                ->description('Ranked list of preferred teachers to cover this teacher’s lessons. Stored as a pivot — managed via the Substitutes tab on the View page.')
                ->collapsed()
                ->schema([
                    Forms\Components\Select::make('preferred_substitute_ids')
                        ->multiple()
                        ->options(fn ($record) => Teacher::query()
                            ->when($record, fn ($q) => $q->whereKeyNot($record->id))
                            ->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->afterStateHydrated(function ($component, $state, $record) {
                            if ($record && empty($state)) {
                                $component->state($record->preferredSubstitutes()->pluck('teachers.id')->all());
                            }
                        })
                        ->dehydrated(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable()->fontFamily('mono'),
                Tables\Columns\ImageColumn::make('avatar_path')
                    ->label('')
                    ->circular()
                    ->disk('public')
                    ->defaultImageUrl(url('/images/avatar-placeholder.svg'))
                    ->size(36),
                Tables\Columns\TextColumn::make('name')->searchable()->weight('bold')
                    ->description(fn ($record) => $record->subject . ' · ' . strtoupper($record->campus ?? '')),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable()
                    ->copyable()
                    ->icon('heroicon-m-envelope')
                    ->color('gray')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('title')->badge()
                    ->formatStateUsing(fn ($state) => Teacher::TITLES[$state] ?? ($state ?: '—'))
                    ->color(fn ($state) => Teacher::TITLE_COLORS[$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->formatStateUsing(fn ($state) => Teacher::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'permanent' => 'success', 'contract' => 'info', 'probation' => 'warning',
                        'opl' => 'warning', 'leave' => 'gray', 'alumni' => 'gray', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('employment')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('tenure'),
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
