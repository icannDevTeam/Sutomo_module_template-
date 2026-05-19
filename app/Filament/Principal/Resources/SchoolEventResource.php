<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\SchoolEventResource\Pages;
use App\Models\SchoolEvent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SchoolEventResource extends Resource
{
    protected static ?string $model = SchoolEvent::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Events';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->required(),
            Forms\Components\TextInput::make('title')->required()->columnSpanFull(),
            Forms\Components\Select::make('category')->options(SchoolEvent::CATEGORIES)->required(),
            Forms\Components\Select::make('campus')->options(['sd'=>'SD','smp'=>'SMP','sma'=>'SMA','int'=>'International'])->required(),
            Forms\Components\DatePicker::make('starts_at')->required(),
            Forms\Components\DatePicker::make('ends_at'),
            Forms\Components\TextInput::make('pic')->label('Person in Charge'),
            Forms\Components\TextInput::make('participants')->numeric(),
            Forms\Components\Select::make('status')->options(SchoolEvent::STATUSES)->default('draft')->required(),
            Forms\Components\Textarea::make('description')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at')
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable(),
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('category')->badge(),
                Tables\Columns\TextColumn::make('campus')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('starts_at')->date(),
                Tables\Columns\TextColumn::make('pic')->placeholder('—'),
                Tables\Columns\TextColumn::make('participants'),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn ($state) => SchoolEvent::STATUS_COLORS[$state] ?? 'gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(SchoolEvent::STATUSES),
                Tables\Filters\SelectFilter::make('category')->options(SchoolEvent::CATEGORIES),
            ])
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSchoolEvents::route('/'),
            'create' => Pages\CreateSchoolEvent::route('/create'),
            'edit'   => Pages\EditSchoolEvent::route('/{record}/edit'),
        ];
    }
}
