<?php

namespace App\Filament\Widgets;

use App\Models\AuditLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentActivity extends BaseWidget
{
    protected static ?string $heading = 'Recent Activity';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 8;

    public function table(Table $table): Table
    {
        return $table
            ->query(AuditLog::query()->latest('occurred_at')->limit(8))
            ->columns([
                Tables\Columns\TextColumn::make('occurred_at')->dateTime('d M · H:i')->color('gray'),
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
                Tables\Columns\TextColumn::make('note')->wrap()->limit(50)->color('gray'),
            ])
            ->paginated(false);
    }
}
