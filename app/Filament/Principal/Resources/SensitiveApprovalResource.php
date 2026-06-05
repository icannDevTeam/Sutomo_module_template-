<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\SensitiveApprovalResource\Pages;
use App\Models\SensitiveApprovalRequest;
use App\Models\Teacher;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SensitiveApprovalResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $model = SensitiveApprovalRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Approvals (HR Sensitive)';
    protected static ?string $navigationGroup = 'Contract Management';
    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('target_teacher_id')
                ->label('Teacher')
                ->options(Teacher::orderBy('name')->pluck('name', 'id'))
                ->searchable()->required()->disabled(fn ($record) => $record !== null),
            Forms\Components\Select::make('action_type')
                ->options(SensitiveApprovalRequest::ACTION_TYPES)
                ->required()->disabled(fn ($record) => $record !== null),
            Forms\Components\KeyValue::make('payload')
                ->keyLabel('Field')->valueLabel('Proposed value')
                ->disabled(fn ($record) => $record !== null),
            Forms\Components\Textarea::make('reason')->rows(3)
                ->disabled(fn ($record) => $record !== null),
            Forms\Components\Select::make('status')
                ->options(SensitiveApprovalRequest::STATUSES)->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('targetTeacher.name')->label('Teacher')->searchable(),
                Tables\Columns\BadgeColumn::make('action_type')->label('Action')
                    ->formatStateUsing(fn ($state) => SensitiveApprovalRequest::ACTION_TYPES[$state] ?? $state),
                Tables\Columns\TextColumn::make('requester.name')->label('Requested by')->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(SensitiveApprovalRequest::STATUS_COLORS)
                    ->formatStateUsing(fn ($state) => SensitiveApprovalRequest::STATUSES[$state] ?? $state),
                Tables\Columns\TextColumn::make('firstApprover.name')->label('1st approver')->toggleable(),
                Tables\Columns\TextColumn::make('secondApprover.name')->label('2nd approver')->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y H:i')->label('Requested'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(SensitiveApprovalRequest::STATUSES),
                Tables\Filters\SelectFilter::make('action_type')->options(SensitiveApprovalRequest::ACTION_TYPES),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => in_array($record->status, ['pending_first', 'pending_second'], true)
                        && $record->requester_id !== Auth::id()
                        && $record->first_approver_id !== Auth::id())
                    ->requiresConfirmation()
                    ->action(fn ($record) => static::approve($record)),
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => in_array($record->status, ['pending_first', 'pending_second'], true))
                    ->form([Forms\Components\Textarea::make('reason')->required()->rows(3)])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['reason'],
                        ]);
                        Notification::make()->title('Request rejected')->danger()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSensitiveApprovals::route('/'),
            'view'   => Pages\ViewSensitiveApproval::route('/{record}'),
        ];
    }

    /**
     * Two-person approval flow. First click = first approver. Second click = second approver.
     * On second approval, applies the payload atomically.
     */
    public static function approve(SensitiveApprovalRequest $req): void
    {
        $userId = Auth::id();

        if ($req->status === 'pending_first') {
            $req->update([
                'first_approver_id' => $userId,
                'first_approved_at' => now(),
                'status' => 'pending_second',
            ]);
            Notification::make()->title('First approval recorded. Awaiting second approver.')->success()->send();
            return;
        }

        if ($req->status === 'pending_second') {
            DB::transaction(function () use ($req, $userId) {
                $req->update([
                    'second_approver_id' => $userId,
                    'second_approved_at' => now(),
                    'status' => 'approved',
                ]);
                static::applyChange($req);
            });
            Notification::make()->title('Second approval — change applied.')->success()->send();
        }
    }

    /**
     * Atomically apply the approved change to the target teacher.
     */
    protected static function applyChange(SensitiveApprovalRequest $req): void
    {
        $teacher = $req->targetTeacher;
        if (!$teacher) return;

        $payload = $req->payload ?? [];

        match ($req->action_type) {
            'salary_change' => \App\Models\TeacherCompensation::create([
                'teacher_id'     => $teacher->id,
                'base_salary'    => (string) ($payload['base_salary'] ?? 0),
                'allowances'     => $payload['allowances'] ?? null,
                'currency'       => $payload['currency'] ?? 'IDR',
                'effective_from' => $payload['effective_from'] ?? now()->toDateString(),
                'notes'          => $payload['notes'] ?? null,
            ]),
            'title_demotion' => $teacher->update(['title' => $payload['title'] ?? $teacher->title]),
            'contract_terminate' => $teacher->update([
                'status'       => 'alumni',
                'contract_end' => $payload['contract_end'] ?? now()->toDateString(),
            ]),
            default => null,
        };

        \App\Models\AuditLog::create([
            'occurred_at' => now(),
            'user_name'   => Auth::user()?->name ?? 'system',
            'role'        => Auth::user()?->role ?? 'system',
            'action'      => 'sensitive_change_applied',
            'target'      => 'Teacher:'.$teacher->id,
            'note'        => "Action: {$req->action_type}",
        ]);
    }
}
