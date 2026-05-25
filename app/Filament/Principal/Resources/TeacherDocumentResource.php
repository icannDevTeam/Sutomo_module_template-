<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\TeacherDocumentResource\Pages;
use App\Models\Teacher;
use App\Models\TeacherDocument;
use App\Support\CsvExporter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TeacherDocumentResource extends Resource
{
    protected static ?string $model = TeacherDocument::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'Document Verifications';
    protected static ?string $navigationGroup = 'Approvals';
    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $c = TeacherDocument::where('status', 'pending')->count();
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
            Forms\Components\Select::make('type')->options(TeacherDocument::TYPES)->required(),
            Forms\Components\TextInput::make('label')->maxLength(120),
            Forms\Components\FileUpload::make('file_path')
                ->disk('public')
                ->directory('teacher-documents')
                ->maxSize(8192) // 8 MB
                ->acceptedFileTypes([
                    'image/png', 'image/jpeg', 'image/webp',
                    'application/pdf',
                ])
                ->preserveFilenames(false),
            Forms\Components\DatePicker::make('expires_at')->label('Expires (optional)'),
            Forms\Components\Select::make('status')->options(TeacherDocument::STATUSES)->default('pending')->required(),
            Forms\Components\Textarea::make('note')->rows(2)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('teacher.name')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('type')->badge()
                    ->formatStateUsing(fn ($state) => TeacherDocument::TYPES[$state] ?? $state),
                Tables\Columns\TextColumn::make('label')->placeholder('—')->limit(40),
                Tables\Columns\TextColumn::make('expires_at')->date()->placeholder('—')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->formatStateUsing(fn ($state) => TeacherDocument::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => TeacherDocument::STATUS_COLORS[$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('verified_by')->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('verified_at')->dateTime()->placeholder('—')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(TeacherDocument::STATUSES)
                    ->default('pending'),
                Tables\Filters\SelectFilter::make('type')->options(TeacherDocument::TYPES),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn ($record) => 'Document · '.($record->teacher?->name ?? 'Teacher'))
                    ->modalWidth('5xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn ($record) => view(
                        'filament.principal.teacher.document-preview',
                        ['record' => $record]
                    ))
                    ->extraModalFooterActions(fn ($record) => $record->status === 'pending' ? [
                        Tables\Actions\Action::make('verifyInModal')
                            ->label('Verify')
                            ->icon('heroicon-o-check-badge')
                            ->color('success')
                            ->requiresConfirmation()
                            ->action(function ($record) {
                                $record->update([
                                    'status'      => 'verified',
                                    'verified_by' => auth()->user()?->name ?? 'Principal',
                                    'verified_at' => now(),
                                ]);
                                Notification::make()->title('Document verified')->success()->send();
                            })
                            ->cancelParentActionOnSuccess(),
                        Tables\Actions\Action::make('rejectInModal')
                            ->label('Reject')
                            ->icon('heroicon-o-x-mark')
                            ->color('danger')
                            ->form([Forms\Components\Textarea::make('note')->label('Rejection reason')->required()])
                            ->action(function ($record, array $data) {
                                $record->update([
                                    'status'      => 'rejected',
                                    'verified_by' => auth()->user()?->name ?? 'Principal',
                                    'verified_at' => now(),
                                    'note'        => $data['note'],
                                ]);
                                Notification::make()->title('Document rejected')->warning()->send();
                            })
                            ->cancelParentActionOnSuccess(),
                    ] : []),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('verify')->icon('heroicon-o-check-badge')->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status'      => 'verified',
                            'verified_by' => auth()->user()?->name ?? 'Principal',
                            'verified_at' => now(),
                        ]);
                        Notification::make()->title('Document verified')->success()->send();
                    }),
                Tables\Actions\Action::make('reject')->icon('heroicon-o-x-mark')->color('danger')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->form([Forms\Components\Textarea::make('note')->label('Rejection reason')->required()])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status'      => 'rejected',
                            'verified_by' => auth()->user()?->name ?? 'Principal',
                            'verified_at' => now(),
                            'note'        => $data['note'],
                        ]);
                        Notification::make()->title('Document rejected')->warning()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('exportCsv')
                    ->label('Export CSV')->icon('heroicon-o-arrow-down-tray')->color('gray')
                    ->action(fn ($records) => CsvExporter::download(
                        $records,
                        [
                            'Teacher'     => fn ($r) => $r->teacher?->name ?? '',
                            'Type'        => fn ($r) => TeacherDocument::TYPES[$r->type] ?? $r->type,
                            'Label'       => 'label',
                            'Expires'     => fn ($r) => optional($r->expires_at)->format('Y-m-d'),
                            'Status'      => fn ($r) => TeacherDocument::STATUSES[$r->status] ?? $r->status,
                            'Verified By' => 'verified_by',
                            'Verified At' => fn ($r) => optional($r->verified_at)->format('Y-m-d H:i'),
                            'Note'        => 'note',
                        ],
                        CsvExporter::filename('teacher-documents'),
                    )),
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTeacherDocuments::route('/'),
            'create' => Pages\CreateTeacherDocument::route('/create'),
            'edit'   => Pages\EditTeacherDocument::route('/{record}/edit'),
        ];
    }
}
