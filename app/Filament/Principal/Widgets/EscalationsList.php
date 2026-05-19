<?php

namespace App\Filament\Principal\Widgets;

use App\Models\BehaviorLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class EscalationsList extends TableWidget
{
    protected static ?string $heading = 'Behavior & SSC Escalations';
    protected static ?int $sort = 5;
    protected int|string|array $columnSpan = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(BehaviorLog::query()->whereIn('status', ['unit_review','vp_review','principal_action'])->latest('occurred_at'))
            ->columns([
                Tables\Columns\TextColumn::make('occurred_at')->date()->label('Date'),
                Tables\Columns\TextColumn::make('student.name')->label('Student')->searchable(),
                Tables\Columns\TextColumn::make('category')->badge(),
                Tables\Columns\TextColumn::make('severity')->badge()
                    ->color(fn ($state) => BehaviorLog::SEVERITY_COLORS[$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->paginated([5, 10]);
    }
}
