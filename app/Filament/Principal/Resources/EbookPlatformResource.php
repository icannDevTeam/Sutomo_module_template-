<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\EbookPlatformResource\Pages;
use App\Models\EbookPlatform;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EbookPlatformResource extends Resource
{
    protected static ?string $model = EbookPlatform::class;
    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationGroup = 'Enrollment';
    protected static ?int $navigationSort = 9;
    protected static ?string $label = 'e-Book Platform';
    protected static ?string $pluralLabel = 'e-Book Platforms';

    public static function shouldRegisterNavigation(): bool
    {
        // Platforms are managed from inside the e-Book Catalog (EbookPackResource).
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Platform')->columns(2)->schema([
                Forms\Components\TextInput::make('name')->required()->placeholder('e.g. Quipper'),
                Forms\Components\TextInput::make('base_url')->label('Base URL')->required()->url()
                    ->placeholder('https://learn.quipper.com'),
                Forms\Components\TextInput::make('logo_url')->label('Logo URL')->url()
                    ->placeholder('https://… (optional)'),
                Forms\Components\Toggle::make('is_active')->default(true),
            ]),
            Forms\Components\Textarea::make('notes')->rows(2)->columnSpanFull()
                ->placeholder('How to log in, support email, etc.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo_url')->label('')->circular()->size(28),
                Tables\Columns\TextColumn::make('name')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('base_url')->url(fn ($record) => $record->base_url, true)
                    ->color('primary')->copyable(),
                Tables\Columns\TextColumn::make('packs_count')->counts('packs')->label('Packs')->alignCenter(),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
            ])
            ->defaultSort('name')
            ->filters([Tables\Filters\TernaryFilter::make('is_active')->default(true)])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->emptyStateHeading('No e-Book platforms')
            ->emptyStateDescription('Add the platforms (Quipper, Ruangguru, Google Classroom…) before building e-Book packs.')
            ->emptyStateIcon('heroicon-o-globe-alt');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEbookPlatforms::route('/'),
            'create' => Pages\CreateEbookPlatform::route('/create'),
            'edit'   => Pages\EditEbookPlatform::route('/{record}/edit'),
        ];
    }
}
