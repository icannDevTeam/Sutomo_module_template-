<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\TeacherLeaveResource\Pages;
use App\Models\Teacher;
use App\Models\TeacherLeave;
use App\Support\CsvExporter;
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
                        Tables\Actions\Action::make('approveInModal')
                            ->label('Approve')->icon('heroicon-o-check')->color('success')
                            ->requiresConfirmation()
                            ->action(function ($record) {
                                $record->update([
                                    'status'     => 'approved',
                                    'decided_by' => auth()->user()?->name ?? 'Principal',
                                    'decided_at' => now(),
                                ]);
                                Notification::make()->title('Leave approved')->success()->send();
                            }),
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')->icon('heroicon-o-check')->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'approved',
                            'decided_by' => auth()->user()?->name ?? 'Principal',
                            'decided_at' => now(),
                        ]);
                        Notification::make()->title('Leave approved')->success()->send();
                    }),
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
}
