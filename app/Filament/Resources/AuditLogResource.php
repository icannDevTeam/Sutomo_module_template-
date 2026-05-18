<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use App\Support\CsvExporter;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Admin';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Audit Log';

    public static function form(Form $form): Form { return $form->schema([]); }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('occurred_at')->dateTime('d M · H:i')->sortable(),
                Tables\Columns\TextColumn::make('user_name')->weight('bold')
                    ->description(fn ($record) => $record->role),
                Tables\Columns\TextColumn::make('action')->badge()->color(fn ($state) => match (true) {
                    str_starts_with($state, 'override') => 'danger',
                    str_starts_with($state, 'approval') => 'success',
                    str_starts_with($state, 'deposit')  => 'warning',
                    str_starts_with($state, 'stage')    => 'info',
                    str_starts_with($state, 'vacancy')  => 'primary',
                    default                              => 'gray',
                }),
                Tables\Columns\TextColumn::make('target')->fontFamily('mono'),
                Tables\Columns\TextColumn::make('from_value')->label('From')->color('gray'),
                Tables\Columns\TextColumn::make('to_value')->label('To')->weight('bold'),
                Tables\Columns\TextColumn::make('note')->wrap()->limit(60),
            ])
            ->defaultSort('occurred_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([
                Tables\Actions\BulkAction::make('exportCsv')
                    ->label('Export selected (CSV)')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('gray')
                    ->deselectRecordsAfterCompletion()
                    ->action(fn ($records) => CsvExporter::download($records, static::csvColumns(), CsvExporter::filename('audit-log'))),
            ])]);
    }

    public static function csvColumns(): array
    {
        return [
            'Occurred at' => 'occurred_at',
            'User'        => 'user_name',
            'Role'        => 'role',
            'Action'      => 'action',
            'Target'      => 'target',
            'From'        => 'from_value',
            'To'          => 'to_value',
            'Note'        => 'note',
        ];
    }

    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

    public static function getPages(): array
    {
        return ['index' => Pages\ListAuditLogs::route('/')];
    }
}
