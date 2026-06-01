<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\TeacherLeaveResource\Pages;
use App\Models\DutyAssignment;
use App\Models\SubstituteOffer;
use App\Models\Teacher;
use App\Models\TeacherLeave;
use App\Support\CsvExporter;
use App\Support\SubstituteBroadcaster;
use App\Support\SubstituteSuggester;
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
    protected static ?string $navigationGroup = 'Approvals';
    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $count = TeacherLeave::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

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
            Forms\Components\Section::make('Auto Substitute Search')
                ->description('When enabled, eligible internal teachers receive an email and can express interest in covering. The principal still picks the final substitute.')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\Toggle::make('auto_search_enabled')
                        ->label('Enable auto substitute search on submit')
                        ->default(true)
                        ->helperText('Turn off to handle substitute assignment entirely manually.'),
                ])
                ->collapsible(),
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
                Tables\Columns\TextColumn::make('coverage')
                    ->label('Coverage')
                    ->badge()
                    ->state(fn ($record) => $record->coverageStatus())
                    ->color(fn (string $state) => match ($state) {
                        'covered'        => 'success',
                        'interested'     => 'info',
                        'searching'      => 'warning',
                        'search_closed'  => 'gray',
                        'unbroadcast'    => 'gray',
                        'none'           => 'danger',
                        default          => 'gray',
                    })
                    ->formatStateUsing(function (string $state, $record) {
                        if ($state === 'interested') {
                            $count = $record->offers()->where('status', 'interested')->count();
                            return "{$count} interested";
                        }
                        return match ($state) {
                            'covered'       => 'Covered',
                            'searching'     => 'Searching',
                            'search_closed' => 'Search closed',
                            'unbroadcast'   => 'Not searched',
                            'none'          => 'No cover',
                            default         => $state,
                        };
                    }),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn ($state) => TeacherLeave::STATUS_COLORS[$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('decided_by')->placeholder('—')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(TeacherLeave::STATUSES),
                Tables\Filters\SelectFilter::make('type')->options(TeacherLeave::TYPES),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')->icon('heroicon-o-eye')->color('gray')
                    ->modalHeading(fn ($record) => 'Leave · ' . ($record->teacher?->name ?? 'Teacher'))
                    ->modalWidth('3xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn ($record) => view('filament.principal.teacher.leave-detail', ['record' => $record]))
                    ->extraModalFooterActions(fn ($record) => $record->status === 'pending' ? [
                        self::approveWithSubstituteAction('approveInModal'),
                        self::pickInterestedAction('pickInterestedInModal'),
                        self::startSearchAction('startSearchInModal'),
                        self::closeSearchAction('closeSearchInModal'),
                        Tables\Actions\Action::make('rejectInModal')
                            ->label('Reject')->icon('heroicon-o-x-mark')->color('danger')
                            ->requiresConfirmation()
                            ->action(function ($record) {
                                $record->update([
                                    'status'     => 'rejected',
                                    'decided_by' => auth()->user()?->name ?? 'Principal',
                                    'decided_at' => now(),
                                ]);
                                Notification::make()->title('Leave rejected')->warning()->send();
                            }),
                    ] : []),
                Tables\Actions\Action::make('printLetter')
                    ->label('Print Letter')->icon('heroicon-o-printer')->color('gray')
                    ->visible(fn ($record) => $record->status === 'approved')
                    ->url(fn ($record) => route('teacher-leave.print', $record))
                    ->openUrlInNewTab(),
                self::startSearchAction(),
                self::closeSearchAction(),
                self::pickInterestedAction(),
                Tables\Actions\EditAction::make(),
                self::approveWithSubstituteAction('approve'),
                Tables\Actions\Action::make('reject')->icon('heroicon-o-x-mark')->color('danger')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'rejected',
                            'decided_by' => auth()->user()?->name ?? 'Principal',
                            'decided_at' => now(),
                        ]);
                        Notification::make()->title('Leave rejected')->warning()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('exportCsv')
                    ->label('Export CSV')->icon('heroicon-o-arrow-down-tray')->color('gray')
                    ->action(fn ($records) => CsvExporter::download(
                        $records,
                        [
                            'Teacher'    => fn ($r) => $r->teacher?->name ?? '',
                            'Type'       => fn ($r) => TeacherLeave::TYPES[$r->type] ?? $r->type,
                            'From'       => fn ($r) => optional($r->starts_at)->format('Y-m-d'),
                            'To'         => fn ($r) => optional($r->ends_at)->format('Y-m-d'),
                            'Days'       => fn ($r) => $r->starts_at && $r->ends_at ? $r->starts_at->diffInDays($r->ends_at) + 1 : '',
                            'Substitute' => fn ($r) => $r->substitute?->name ?? '',
                            'Status'     => fn ($r) => TeacherLeave::STATUSES[$r->status] ?? $r->status,
                            'Decided By' => 'decided_by',
                            'Reason'     => 'reason',
                        ],
                        CsvExporter::filename('teacher-leaves'),
                    )),
                Tables\Actions\DeleteBulkAction::make(),
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

    /**
     * Approve action with a candidate-picker modal.
     * Used both as a row action and as a modal footer action on the View modal.
     */
    protected static function approveWithSubstituteAction(string $name): Tables\Actions\Action
    {
        return Tables\Actions\Action::make($name)
            ->label('Approve')
            ->icon('heroicon-o-check')
            ->color('success')
            ->visible(fn ($record) => $record->status === 'pending')
            ->modalHeading(fn ($record) => 'Assign Substitute · ' . ($record->teacher?->name ?? 'Teacher'))
            ->modalWidth('5xl')
            ->modalSubmitActionLabel('Approve & Assign')
            ->modalContent(fn ($record) => view('filament.principal.teacher.substitute-picker', [
                'leave'      => $record,
                'candidates' => SubstituteSuggester::for($record),
            ]))
            ->form([
                Forms\Components\Hidden::make('substitute_teacher_id'),
                Forms\Components\Toggle::make('create_duty_assignment')
                    ->label('Also create a DutyAssignment (status = pending) for the substitute')
                    ->default(true),
                Forms\Components\Toggle::make('approve_without_substitute')
                    ->label('Approve without assigning a substitute')
                    ->helperText('Use for mid-day or prior-notice cases where cover is not required.')
                    ->default(false),
            ])
            ->action(function (array $data, $record): void {
                $skipSubstitute = (bool) ($data['approve_without_substitute'] ?? false);
                $substituteId   = $data['substitute_teacher_id'] ?? null;

                if (! $skipSubstitute && ! $substituteId) {
                    Notification::make()
                        ->title('Pick a substitute, or toggle "Approve without assigning a substitute".')
                        ->danger()
                        ->send();
                    return;
                }

                if (! $skipSubstitute && $substituteId) {
                    // Defense in depth: reject if candidate is no longer assignable.
                    $candidates = SubstituteSuggester::for($record, 100);
                    $picked     = $candidates->firstWhere(fn ($c) => (int) $c['teacher']->id === (int) $substituteId);

                    if ($picked && ! $picked['is_assignable']) {
                        Notification::make()
                            ->title('That substitute is no longer available.')
                            ->body(implode(' · ', $picked['conflict_reasons']))
                            ->danger()
                            ->send();
                        return;
                    }
                }

                $record->update([
                    'status'                => 'approved',
                    'substitute_teacher_id' => $skipSubstitute ? null : $substituteId,
                    'decided_by'            => auth()->user()?->name ?? 'Principal',
                    'decided_at'            => now(),
                ]);

                $subName = null;
                if (! $skipSubstitute && $substituteId) {
                    $subName = Teacher::whereKey($substituteId)->value('name');

                    if (($data['create_duty_assignment'] ?? false) && $subName) {
                        DutyAssignment::create([
                            'teacher_id'    => $substituteId,
                            'title'         => 'Cover for ' . ($record->teacher?->name ?? 'teacher'),
                            'starts_at'     => $record->starts_at,
                            'ends_at'       => $record->ends_at,
                            'recurrence'    => 'once',
                            'status'        => 'pending',
                            'assigned_by'   => auth()->user()?->name ?? 'Principal',
                            'academic_year' => DutyAssignment::academicYearFor($record->starts_at),
                        ]);
                    }
                }

                Notification::make()
                    ->title('Leave approved')
                    ->body($subName ? $subName . ' assigned as substitute.' : 'Approved without substitute.')
                    ->success()
                    ->send();
            });
    }

    /**
     * Start (or restart) the auto substitute search. Emails all eligible
     * candidates; interested ones show up in the leave detail for the
     * principal to pick.
     */
    protected static function startSearchAction(string $name = 'startSearch'): Tables\Actions\Action
    {
        return Tables\Actions\Action::make($name)
            ->label(fn ($record) => $record->auto_search_status === 'open' ? 'Re-broadcast' : 'Start Auto Search')
            ->icon('heroicon-o-paper-airplane')
            ->color('info')
            ->visible(fn ($record) => $record->status === 'pending'
                && ! $record->substitute_teacher_id
                && $record->auto_search_status !== 'open')
            ->modalHeading(fn ($record) => 'Start Auto Substitute Search · ' . ($record->teacher?->name ?? 'Teacher'))
            ->modalDescription('Emails eligible internal teachers inviting them to express interest. Search auto-closes when the cap is reached or after the response window.')
            ->modalWidth('xl')
            ->modalSubmitActionLabel('Start Search')
            ->form([
                Forms\Components\TextInput::make('ttl_hours')
                    ->label('Response window (hours)')
                    ->numeric()->minValue(1)->maxValue(72)
                    ->default(SubstituteBroadcaster::DEFAULT_TTL_HOURS)
                    ->required(),
                Forms\Components\TextInput::make('cap')
                    ->label('Max interested teachers before auto-close')
                    ->numeric()->minValue(1)->maxValue(20)
                    ->default(SubstituteBroadcaster::DEFAULT_CAP)
                    ->required(),
                Forms\Components\TextInput::make('invite_limit')
                    ->label('Max teachers to email')
                    ->numeric()->minValue(1)->maxValue(50)
                    ->default(SubstituteBroadcaster::DEFAULT_INVITE_LIMIT)
                    ->required(),
            ])
            ->action(function (array $data, $record): void {
                $offers = SubstituteBroadcaster::startAutoSearch(
                    leave: $record,
                    ttlHours: (int) ($data['ttl_hours'] ?? SubstituteBroadcaster::DEFAULT_TTL_HOURS),
                    cap: (int) ($data['cap'] ?? SubstituteBroadcaster::DEFAULT_CAP),
                    inviteLimit: (int) ($data['invite_limit'] ?? SubstituteBroadcaster::DEFAULT_INVITE_LIMIT),
                );

                if (empty($offers)) {
                    Notification::make()->title('No new candidates to invite')
                        ->body('All eligible teachers have already been invited.')
                        ->warning()->send();
                    return;
                }

                Notification::make()
                    ->title('Auto search started')
                    ->body(count($offers) . ' teacher(s) invited to express interest.')
                    ->success()
                    ->send();
            });
    }

    /**
     * Manually close the auto substitute search.
     */
    protected static function closeSearchAction(string $name = 'closeSearch'): Tables\Actions\Action
    {
        return Tables\Actions\Action::make($name)
            ->label('Close Search')
            ->icon('heroicon-o-x-circle')
            ->color('gray')
            ->visible(fn ($record) => $record->status === 'pending'
                && $record->auto_search_status === 'open')
            ->requiresConfirmation()
            ->modalHeading('Close auto substitute search?')
            ->modalDescription('Pending invitations will be cancelled. Teachers who already expressed interest will still be visible so you can assign one.')
            ->action(function ($record): void {
                SubstituteBroadcaster::closeAutoSearch($record, 'closed_manual');
                Notification::make()->title('Auto search closed')->success()->send();
            });
    }

    /**
     * Modal listing teachers who expressed interest, with radio + Assign.
     */
    protected static function pickInterestedAction(string $name = 'pickInterested'): Tables\Actions\Action
    {
        return Tables\Actions\Action::make($name)
            ->label(fn ($record) => 'Pick Substitute (' . $record->offers()->where('status', 'interested')->count() . ')')
            ->icon('heroicon-o-user-plus')
            ->color('success')
            ->visible(fn ($record) => $record->status === 'pending'
                && ! $record->substitute_teacher_id
                && $record->offers()->where('status', 'interested')->exists())
            ->modalHeading(fn ($record) => 'Pick Substitute · ' . ($record->teacher?->name ?? 'Teacher'))
            ->modalDescription('Choose one of the teachers who expressed interest. The other interested teachers will be notified the slot is filled.')
            ->modalWidth('2xl')
            ->modalSubmitActionLabel('Assign as Substitute')
            ->form(function ($record) {
                $offers = $record->offers()->with('teacher')
                    ->where('status', 'interested')
                    ->orderBy('responded_at')
                    ->get();

                $options = $offers->mapWithKeys(function ($o) {
                    $t = $o->teacher;
                    if (! $t) return [];
                    $when = $o->responded_at ? $o->responded_at->diffForHumans() : '';
                    return [$o->id => "{$t->name} · {$t->subject} · expressed interest {$when}"];
                })->all();

                return [
                    Forms\Components\Radio::make('offer_id')
                        ->label('Interested teachers')
                        ->options($options)
                        ->required(),
                ];
            })
            ->action(function (array $data, $record): void {
                $offerId = (int) ($data['offer_id'] ?? 0);
                $assigned = SubstituteBroadcaster::assignFromInterested($record, $offerId);

                if (! $assigned) {
                    Notification::make()->title('Could not assign')
                        ->body('That teacher is no longer interested.')
                        ->danger()->send();
                    return;
                }

                Notification::make()
                    ->title('Substitute assigned')
                    ->body(($assigned->teacher?->name ?? 'Teacher') . ' is now the substitute. Other interested teachers were notified.')
                    ->success()
                    ->send();
            });
    }
}
