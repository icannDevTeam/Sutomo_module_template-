<?php

namespace App\Filament\Principal\Widgets;

use App\Models\SchoolEvent;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class UpcomingActivities extends TableWidget
{
    protected static ?string $heading = 'Upcoming School Events';
    protected static ?int $sort = 6;
    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(SchoolEvent::query()->where('starts_at', '>=', now()->startOfDay())->orderBy('starts_at'))
            ->columns([
                Tables\Columns\TextColumn::make('starts_at')->date('d M'),
                Tables\Columns\TextColumn::make('title')->wrap(),
                Tables\Columns\TextColumn::make('campus')->badge()->color('gray'),
            ])
            ->paginated([5]);
    }
}
