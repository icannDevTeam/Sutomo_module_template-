<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\SscRequestResource\Pages;
use App\Models\SscRequest;
use App\Models\Student;
use App\Support\CsvExporter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SscRequestResource extends Resource
{
    protected static ?string $model = SscRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'SSC Requests';
    protected static ?string $navigationGroup = 'Approvals';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->required(),
            Forms\Components\Select::make('student_id')->label('Student')
                ->options(fn () => Student::orderBy('name')->pluck('name','id'))
                ->searchable()->required(),
            Forms\Components\Select::make('type')->options(SscRequest::TYPES)->required(),
            Forms\Components\Select::make('priority')->options(['low'=>'Low','normal'=>'Normal','high'=>'High'])->default('normal'),
            Forms\Components\Select::make('status')->options(SscRequest::STATUSES)->default('pending')->required(),
            Forms\Components\DatePicker::make('requested_at')->default(now())->required(),
            Forms\Components\Textarea::make('notes')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('requested_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable(),
                Tables\Columns\TextColumn::make('student.name')->searchable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('priority')->badge()
                    ->color(fn ($state) => $state === 'high' ? 'danger' : ($state === 'normal' ? 'gray' : 'info')),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn ($state) => SscRequest::STATUS_COLORS[$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('requested_at')->date(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(SscRequest::STATUSES),
                Tables\Filters\SelectFilter::make('type')->options(SscRequest::TYPES),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')->icon('heroicon-o-eye')->color('gray')
                    ->modalHeading(fn ($record) => 'Request · ' . $record->code)
                    ->modalWidth('3xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn ($record) => view('filament.principal.ssc.ssc-detail', ['record' => $record]))
                    ->extraModalFooterActions(fn ($record) => $record->status === 'pending' ? [
                        Tables\Actions\Action::make('approveInModal')
                            ->label('Approve')->icon('heroicon-o-check')->color('success')
                            ->requiresConfirmation()
                            ->action(function ($record) {
                                $record->update(['status' => 'approved']);
                                Notification::make()->title('Request approved')->success()->send();
                            }),
                        Tables\Actions\Action::make('rejectInModal')
                            ->label('Reject')->icon('heroicon-o-x-mark')->color('danger')
                            ->form([Forms\Components\Textarea::make('notes')->label('Rejection reason')->required()])
                            ->action(function ($record, array $data) {
                                $record->update([
                                    'status' => 'rejected',
                                    'notes'  => $data['notes'],
                                ]);
                                Notification::make()->title('Request rejected')->warning()->send();
                            }),
                    ] : []),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('exportCsv')
                    ->label('Export CSV')->icon('heroicon-o-arrow-down-tray')->color('gray')
                    ->action(fn ($records) => CsvExporter::download(
                        $records,
                        [
                            'Code'      => 'code',
                            'Student'   => fn ($r) => $r->student?->name ?? '',
                            'Type'      => fn ($r) => SscRequest::TYPES[$r->type] ?? $r->type,
                            'Priority'  => 'priority',
                            'Status'    => fn ($r) => SscRequest::STATUSES[$r->status] ?? $r->status,
                            'Requested' => fn ($r) => optional($r->requested_at)->format('Y-m-d'),
                            'Notes'     => 'notes',
                        ],
                        CsvExporter::filename('ssc-requests'),
                    )),
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSscRequests::route('/'),
            'create' => Pages\CreateSscRequest::route('/create'),
            'edit'   => Pages\EditSscRequest::route('/{record}/edit'),
        ];
    }
}
