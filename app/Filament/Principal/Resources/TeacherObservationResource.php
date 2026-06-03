<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\TeacherObservationResource\Pages;
use App\Models\Teacher;
use App\Models\TeacherObservation;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Table;
use App\Support\CsvExporter;
use Illuminate\Database\Eloquent\Collection;

class TeacherObservationResource extends Resource
{
    protected static ?string $model = TeacherObservation::class;
    protected static ?string $navigationIcon = 'heroicon-o-eye';
    protected static ?string $navigationLabel = 'Teacher Observations';
    protected static ?string $navigationGroup = 'Supervision';
    protected static ?int $navigationSort = 2;

    public const STATUS_COLORS = [
        'pending'  => 'warning',
        'approved' => 'success',
        'rejected' => 'danger',
    ];

    public static function form(Form $form): Form
    {
        $criteria = TeacherObservation::criteriaLabels();

        $criteriaGroups = collect($criteria)->map(function (string $label, string $key) {
            return Forms\Components\Group::make([
                Forms\Components\Select::make("dimensions.$key")
                    ->label($label)
                    ->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'])
                    ->placeholder('Score'),
                Forms\Components\Textarea::make("notes_by_criterion.$key")
                    ->label('Notes')
                    ->rows(2)
                    ->placeholder("Notes for {$label}"),
            ])->columns(2);
        })->values()->all();

        return $form->schema([
            Forms\Components\Section::make('Lesson')->columns(2)->schema([
                Forms\Components\Select::make('teacher_id')->label('Observee')
                    ->options(fn () => Teacher::orderBy('name')->pluck('name', 'id'))
                    ->searchable()->required(),
                Forms\Components\Select::make('observer_id')->label('Observer')
                    ->options(fn () => User::orderBy('name')->pluck('name', 'id'))
                    ->default(fn () => auth()->id())
                    ->searchable()->required(),
                Forms\Components\DateTimePicker::make('observed_at')->required()->default(now()),
                Forms\Components\TextInput::make('lesson_subject'),
                Forms\Components\TextInput::make('lesson_class_code')->label('Class'),
            ]),
            Forms\Components\Section::make('Criteria (1–5)')
                ->columns(1)
                ->schema($criteriaGroups),
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
                Tables\Columns\TextColumn::make('teacher.name')->label('Observee')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('lesson_subject')->placeholder('—'),
                Tables\Columns\TextColumn::make('lesson_class_code')->label('Class')->placeholder('—'),
                Tables\Columns\TextColumn::make('average_score')->label('Avg')->badge()
                    ->color(fn ($state) => $state === null ? 'gray' : ($state >= 4 ? 'success' : ($state >= 3 ? 'warning' : 'danger'))),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state ? ucfirst($state) : '—')
                    ->color(fn (?string $state) => self::STATUS_COLORS[$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('observer.name')->label('Observer')->placeholder('—'),
                Tables\Columns\TextColumn::make('follow_up_date')->date()->placeholder('—')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('teacher_id')
                    ->label('Observee')
                    ->options(fn () => Teacher::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending'  => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (TeacherObservation $record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('review_notes')->rows(3),
                    ])
                    ->action(function (TeacherObservation $record, array $data) {
                        self::transitionStatus($record, 'approved', $data['review_notes'] ?? null);
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (TeacherObservation $record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('review_notes')->rows(3)->required(),
                    ])
                    ->action(function (TeacherObservation $record, array $data) {
                        self::transitionStatus($record, 'rejected', $data['review_notes']);
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('exportCsv')
                        ->label('Export CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (Collection $records) {
                            $records->loadMissing(['teacher', 'observer']);
                            return CsvExporter::download(
                                $records,
                                [
                                    'observed_at'       => fn ($r) => optional($r->observed_at)->format('Y-m-d H:i'),
                                    'teacher'           => fn ($r) => optional($r->teacher)->name,
                                    'observer'          => fn ($r) => optional($r->observer)->name,
                                    'lesson_subject'    => 'lesson_subject',
                                    'lesson_class_code' => 'lesson_class_code',
                                    'average_score'     => fn ($r) => $r->average_score,
                                    'status'            => 'status',
                                ],
                                CsvExporter::filename('observations'),
                            );
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function transitionStatus(TeacherObservation $record, string $status, ?string $notes): void
    {
        // State guard: only pending observations may be approved/rejected.
        abort_unless($record->status === 'pending', 409, 'Observation already reviewed.');
        // Separation-of-duties: observer may not review own observation.
        abort_if($record->observer_id !== null && $record->observer_id === auth()->id(), 403, 'Observer cannot review own observation.');
        // Role gate: only principal/vice_principal may approve/reject.
        $role = auth()->user()->role ?? null;
        abort_unless(in_array($role, ['principal', 'vice_principal', 'admin', 'superadmin'], true), 403, 'Insufficient role to review observations.');

        $from = $record->status;
        $record->forceFill([
            'status'       => $status,
            'reviewed_by'  => auth()->id(),
            'reviewed_at'  => now(),
            'review_notes' => $notes,
        ])->save();

        \Filament\Notifications\Notification::make()
            ->title('Observation ' . $status)
            ->success()
            ->send();

        if (class_exists(\App\Models\AuditLog::class)) {
            try {
                \App\Models\AuditLog::create([
                    'occurred_at' => now(),
                    'user_name'   => optional(auth()->user())->name ?? 'system',
                    'role'        => 'principal',
                    'action'      => 'observation.' . $status,
                    'target'      => 'TeacherObservation:' . $record->id,
                    'from_value'  => $from,
                    'to_value'    => $status,
                    'note'        => $notes,
                ]);
            } catch (\Throwable $e) {
                // audit failure must not block the action
            }
        }
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTeacherObservations::route('/'),
            'create' => Pages\CreateTeacherObservation::route('/create'),
            'view'   => Pages\ViewTeacherObservation::route('/{record}'),
            'edit'   => Pages\EditTeacherObservation::route('/{record}/edit'),
        ];
    }
}
