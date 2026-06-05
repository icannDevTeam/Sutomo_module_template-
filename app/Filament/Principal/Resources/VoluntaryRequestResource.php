<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\VoluntaryRequestResource\Pages;
use App\Models\Teacher;
use App\Models\VoluntaryRequest;
use App\Support\CsvExporter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VoluntaryRequestResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $model = VoluntaryRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-hand-raised';
    protected static ?string $navigationLabel = 'Voluntary Requests';
    protected static ?string $navigationGroup = 'Contract Management';
    protected static ?int $navigationSort = 5;

    public static function canViewAny(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $c = VoluntaryRequest::where('status', 'pending')->count();
        return $c > 0 ? (string) $c : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('teacher_id')->label('Teacher')
                ->options(fn () => Teacher::orderBy('name')->pluck('name', 'id'))
                ->searchable()->required(),
            Forms\Components\TextInput::make('program')->required()->maxLength(150),
            Forms\Components\Textarea::make('reason')->rows(3)->columnSpanFull(),
            Forms\Components\Select::make('status')->options(VoluntaryRequest::STATUSES)->default('pending')->required(),
            Forms\Components\Textarea::make('decision_note')->rows(2)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('submitted_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('teacher.name')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('program')->searchable()->limit(50),
                Tables\Columns\TextColumn::make('reason')->limit(60)->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('submitted_at')->dateTime('d M Y')->placeholder('—'),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->formatStateUsing(fn ($state) => VoluntaryRequest::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => VoluntaryRequest::STATUS_COLORS[$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('decided_by')->placeholder('—')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(VoluntaryRequest::STATUSES)
                    ->default('pending'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')->icon('heroicon-o-check')->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->form([Forms\Components\Textarea::make('decision_note')->label('Note (optional)')])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status'        => 'approved',
                            'decided_by'    => auth()->user()?->name ?? 'Principal',
                            'decided_at'    => now(),
                            'decision_note' => $data['decision_note'] ?? null,
                        ]);
                        Notification::make()->title('Request approved')->success()->send();
                    }),
                Tables\Actions\Action::make('decline')->icon('heroicon-o-x-mark')->color('danger')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->form([Forms\Components\Textarea::make('decision_note')->label('Reason')->required()])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status'        => 'declined',
                            'decided_by'    => auth()->user()?->name ?? 'Principal',
                            'decided_at'    => now(),
                            'decision_note' => $data['decision_note'],
                        ]);
                        Notification::make()->title('Request declined')->warning()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('exportCsv')
                    ->label('Export CSV')->icon('heroicon-o-arrow-down-tray')->color('gray')
                    ->action(fn ($records) => CsvExporter::download(
                        $records,
                        [
                            'Teacher'   => fn ($r) => $r->teacher?->name ?? '',
                            'Type'      => 'type',
                            'Title'     => 'title',
                            'Status'    => fn ($r) => VoluntaryRequest::STATUSES[$r->status] ?? $r->status,
                            'Decided By' => 'decided_by',
                            'Decided At' => fn ($r) => optional($r->decided_at)->format('Y-m-d H:i'),
                            'Note'      => 'decision_note',
                        ],
                        CsvExporter::filename('voluntary-requests'),
                    )),
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVoluntaryRequests::route('/'),
            'create' => Pages\CreateVoluntaryRequest::route('/create'),
            'edit'   => Pages\EditVoluntaryRequest::route('/{record}/edit'),
        ];
    }
}
