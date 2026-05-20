<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\ProcurementRequestResource\Pages;
use App\Models\ProcurementRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProcurementRequestResource extends Resource
{
    protected static ?string $model = ProcurementRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Procurement';
    protected static ?string $navigationGroup = 'Approvals';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->required(),
            Forms\Components\TextInput::make('title')->required()->columnSpanFull(),
            Forms\Components\Select::make('category')->options(ProcurementRequest::CATEGORIES)->required(),
            Forms\Components\Select::make('campus')->label('Unit')->options(\App\Support\SchoolDirectory::unitOptions())->required(),
            Forms\Components\TextInput::make('amount')->numeric()->prefix('Rp'),
            Forms\Components\DatePicker::make('needed_by'),
            Forms\Components\TextInput::make('requested_by'),
            Forms\Components\Select::make('status')->options(ProcurementRequest::STATUSES)->default('pending')->required(),
            Forms\Components\Textarea::make('description')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable(),
                Tables\Columns\TextColumn::make('title')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('category')->badge(),
                Tables\Columns\TextColumn::make('campus')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('amount')->prefix('Rp ')->numeric(0)->sortable(),
                Tables\Columns\TextColumn::make('needed_by')->date(),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn ($state) => ProcurementRequest::STATUS_COLORS[$state] ?? 'gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(ProcurementRequest::STATUSES),
                Tables\Filters\SelectFilter::make('category')->options(ProcurementRequest::CATEGORIES),
            ])
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProcurementRequests::route('/'),
            'create' => Pages\CreateProcurementRequest::route('/create'),
            'edit'   => Pages\EditProcurementRequest::route('/{record}/edit'),
        ];
    }
}
