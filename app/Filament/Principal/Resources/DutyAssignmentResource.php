<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\DutyAssignmentResource\Pages;
use App\Models\DutyAssignment;
use App\Models\Teacher;
use App\Support\CsvExporter;
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
    protected static ?string $navigationGroup = 'Leave & Substitution';
    protected static ?int $navigationSort = 4;

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

            Forms\Components\Select::make('recurrence')
                ->options(DutyAssignment::RECURRENCES)
                ->default('once')
                ->required()
                ->reactive(),

            Forms\Components\CheckboxList::make('days_of_week')
                ->label('Days of the week')
                ->options(DutyAssignment::DAYS_LONG)
                ->columns(4)
                ->visible(fn ($get) => $get('recurrence') === 'weekly')
                ->helperText('Pick weekdays this duty repeats on.'),

            Forms\Components\Select::make('academic_year')
                ->options(fn () => DutyAssignment::availableAcademicYears())
                ->default(fn () => DutyAssignment::currentAcademicYear())
                ->searchable()
                ->required(),

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

                Tables\Columns\TextColumn::make('days_of_week')
                    ->label('Days')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->recurrence !== 'weekly' || empty($state)) {
                            return '—';
                        }
                        $days = is_array($state) ? $state : (array) $state;
                        sort($days);
                        return collect($days)
                            ->map(fn ($d) => DutyAssignment::DAYS[(int) $d] ?? null)
                            ->filter()
                            ->join(' · ');
                    })
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('recurrence')
                    ->badge()
                    ->color(fn ($state) => $state === 'weekly' ? 'info' : 'gray')
                    ->formatStateUsing(fn ($state) => DutyAssignment::RECURRENCES[$state] ?? $state)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('academic_year')->label('AY')->sortable()->toggleable(),

                Tables\Columns\TextColumn::make('starts_at')->dateTime('d M, H:i')->sortable(),
                Tables\Columns\TextColumn::make('ends_at')->dateTime('d M, H:i')->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('assigned_by')->placeholder('—')->toggleable(),

                Tables\Columns\TextColumn::make('status')->badge()
                    ->formatStateUsing(fn ($state) => DutyAssignment::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => DutyAssignment::STATUS_COLORS[$state] ?? 'gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(DutyAssignment::STATUSES),
                Tables\Filters\SelectFilter::make('recurrence')->options(DutyAssignment::RECURRENCES),
                Tables\Filters\SelectFilter::make('academic_year')
                    ->label('Academic Year')
                    ->options(fn () => DutyAssignment::availableAcademicYears()),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn ($record) => 'Duty · '.$record->title)
                    ->modalWidth('5xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn ($record) => view(
                        'filament.principal.teacher.duty-detail',
                        ['record' => $record]
                    ))
                    ->extraModalFooterActions(fn ($record) => $record->status === 'accepted' ? [
                        Tables\Actions\Action::make('completeInModal')
                            ->label('Mark Completed')
                            ->icon('heroicon-o-check-circle')
                            ->color('success')
                            ->requiresConfirmation()
                            ->action(function ($record) {
                                $record->update(['status' => 'completed']);
                                Notification::make()->title('Duty marked completed')->success()->send();
                            }),
                    ] : []),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('printBriefing')
                    ->label('Print Briefing')->icon('heroicon-o-printer')->color('gray')
                    ->url(fn ($record) => route('duty-assignment.print', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('markCompleted')->icon('heroicon-o-check-circle')->color('success')
                    ->label('Mark completed')
                    ->visible(fn ($record) => $record->status === 'accepted')
                    ->action(function ($record) {
                        $record->update(['status' => 'completed']);
                        Notification::make()->title('Duty marked completed')->success()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('exportCsv')
                    ->label('Export CSV')->icon('heroicon-o-arrow-down-tray')->color('gray')
                    ->action(fn ($records) => CsvExporter::download(
                        $records,
                        [
                            'Title'         => 'title',
                            'Teacher'       => fn ($r) => $r->teacher?->name ?? '',
                            'Recurrence'    => fn ($r) => DutyAssignment::RECURRENCES[$r->recurrence] ?? $r->recurrence,
                            'Days of Week'  => fn ($r) => collect(is_array($r->days_of_week) ? $r->days_of_week : [])
                                ->map(fn ($d) => DutyAssignment::DAYS[(int) $d] ?? $d)->implode(', '),
                            'Academic Year' => 'academic_year',
                            'Location'      => 'location',
                            'Starts'        => fn ($r) => optional($r->starts_at)->format('Y-m-d H:i'),
                            'Ends'          => fn ($r) => optional($r->ends_at)->format('Y-m-d H:i'),
                            'Status'        => fn ($r) => DutyAssignment::STATUSES[$r->status] ?? $r->status,
                        ],
                        CsvExporter::filename('duty-assignments'),
                    )),
                Tables\Actions\DeleteBulkAction::make(),
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
