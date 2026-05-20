<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\BookPackageResource\Pages;
use App\Models\BookPackage;
use App\Support\SchoolDirectory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BookPackageResource extends Resource
{
    protected static ?string $model = BookPackage::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Enrollment';
    protected static ?int $navigationSort = 6;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $label = 'Book Package';
    protected static ?string $pluralLabel = 'Book Packages';
    protected static ?string $navigationLabel = 'Book Catalog';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Cohort')
                ->description('A package is uniquely identified by Unit + Grade + School Year.')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->placeholder('e.g. SD Grade 1 — Paket Buku 2026/2027')
                        ->required()
                        ->columnSpanFull(),
                    Forms\Components\Select::make('unit')
                        ->options(SchoolDirectory::unitOptions())
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('grade', null)),
                    Forms\Components\Select::make('grade')
                        ->options(fn (Forms\Get $get) => SchoolDirectory::gradesForUnit($get('unit')))
                        ->required()
                        ->disabled(fn (Forms\Get $get) => ! $get('unit')),
                    Forms\Components\TextInput::make('school_year')
                        ->default('2026/2027')
                        ->placeholder('2026/2027')
                        ->required(),
                    Forms\Components\Toggle::make('is_active')
                        ->default(true)
                        ->helperText('Only active packages appear in the onboarding picker.'),
                ]),

            Forms\Components\Section::make('Book line-items')
                ->description('Drag to reorder. Subtotals are summed automatically.')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->label('')
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->required()
                                ->placeholder('e.g. Matematika Kelas 1 — Erlangga')
                                ->columnSpan(2),
                            Forms\Components\TextInput::make('qty')
                                ->numeric()->minValue(1)->default(1)->required(),
                            Forms\Components\TextInput::make('unit_price')
                                ->label('Unit price (Rp)')
                                ->numeric()->minValue(0)->required()
                                ->prefix('Rp'),
                        ])
                        ->columns(4)
                        ->defaultItems(1)
                        ->reorderable()
                        ->collapsible()
                        ->cloneable()
                        ->itemLabel(function (array $state): ?string {
                            $sub = (int) ($state['qty'] ?? 0) * (int) ($state['unit_price'] ?? 0);
                            $title = $state['title'] ?? 'New item';
                            return $sub > 0 ? "{$title} · Rp " . number_format($sub, 0, ',', '.') : $title;
                        })
                        ->addActionLabel('Add book')
                        ->required(),
                ]),

            Forms\Components\Section::make('Notes')
                ->collapsed()
                ->schema([
                    Forms\Components\Textarea::make('notes')->label('')->rows(3),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->weight('bold')->wrap(),
                Tables\Columns\TextColumn::make('unit')->badge()
                    ->formatStateUsing(fn ($state) => SchoolDirectory::unitLabel($state) ?? '—')
                    ->color('info'),
                Tables\Columns\TextColumn::make('grade')->badge()->alignCenter(),
                Tables\Columns\TextColumn::make('school_year')->alignCenter(),
                Tables\Columns\TextColumn::make('items')->label('Books')->alignCenter()
                    ->state(fn (BookPackage $r) => count($r->items ?? [])),
                Tables\Columns\TextColumn::make('total')->money('IDR', divideBy: 1)->alignEnd()->weight('bold'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
                Tables\Columns\TextColumn::make('updated_at')->since()->label('Updated')->toggleable(),
            ])
            ->defaultSort('unit')
            ->filters([
                Tables\Filters\SelectFilter::make('unit')->options(SchoolDirectory::unitOptions()),
                Tables\Filters\SelectFilter::make('school_year')
                    ->options(fn () => BookPackage::query()->distinct()->pluck('school_year', 'school_year')->toArray()),
                Tables\Filters\TernaryFilter::make('is_active')->default(true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ReplicateAction::make()
                    ->excludeAttributes(['total'])
                    ->beforeReplicaSaved(function (BookPackage $replica) {
                        $replica->name = $replica->name . ' (Copy)';
                        $replica->is_active = false;
                    }),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->emptyStateHeading('No book packages yet')
            ->emptyStateDescription('Create one per (Unit + Grade) cohort. Students in onboarding will pick from active packages.')
            ->emptyStateIcon('heroicon-o-book-open');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBookPackages::route('/'),
            'create' => Pages\CreateBookPackage::route('/create'),
            'edit'   => Pages\EditBookPackage::route('/{record}/edit'),
        ];
    }
}
