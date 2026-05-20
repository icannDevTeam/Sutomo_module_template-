<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\EbookPackResource\Pages;
use App\Models\EbookPack;
use App\Models\EbookPlatform;
use App\Support\SchoolDirectory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EbookPackResource extends Resource
{
    protected static ?string $model = EbookPack::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Enrollment';
    protected static ?int $navigationSort = 8;
    protected static ?string $label = 'e-Book Pack';
    protected static ?string $pluralLabel = 'e-Book Packs';
    protected static ?string $navigationLabel = 'e-Book Catalog';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Cohort')
                ->description('A pack is uniquely identified by Unit + Grade + School Year.')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->placeholder('e.g. SD Grade 1 — Digital Library 2026/2027')
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
                    Forms\Components\Select::make('default_platform_id')
                        ->label('Default platform')
                        ->options(EbookPlatform::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->helperText('Optional. Shown as the pack header in the onboarding modal.'),
                    Forms\Components\Toggle::make('is_active')->default(true),
                ]),

            Forms\Components\Section::make('e-Book links')
                ->description('Each row becomes a click-through link in the student/parent view.')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->label('')
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->required()
                                ->placeholder('e.g. Matematika Kelas 1')
                                ->columnSpan(2),
                            Forms\Components\Select::make('platform_id')
                                ->label('Platform')
                                ->options(EbookPlatform::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                                ->searchable()
                                ->required(),
                            Forms\Components\TextInput::make('url')
                                ->label('URL')
                                ->url()
                                ->required()
                                ->placeholder('https://…')
                                ->columnSpan(2),
                            Forms\Components\TextInput::make('login_hint')
                                ->label('Login hint (optional)')
                                ->placeholder('class code / shared login'),
                        ])
                        ->columns(3)
                        ->defaultItems(1)
                        ->reorderable()
                        ->collapsible()
                        ->cloneable()
                        ->itemLabel(function (array $state): ?string {
                            $title = $state['title'] ?? 'New e-book';
                            $platform = $state['platform_id']
                                ? (EbookPlatform::find($state['platform_id'])?->name ?? '')
                                : '';
                            return $platform ? "{$title} · {$platform}" : $title;
                        })
                        ->addActionLabel('Add e-Book')
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
                Tables\Columns\TextColumn::make('defaultPlatform.name')->label('Platform')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('items')->label('Links')->alignCenter()
                    ->state(fn (EbookPack $r) => count($r->items ?? [])),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
                Tables\Columns\TextColumn::make('updated_at')->since()->label('Updated')->toggleable(),
            ])
            ->defaultSort('unit')
            ->filters([
                Tables\Filters\SelectFilter::make('unit')->options(SchoolDirectory::unitOptions()),
                Tables\Filters\SelectFilter::make('default_platform_id')->label('Platform')
                    ->options(EbookPlatform::query()->pluck('name', 'id')),
                Tables\Filters\TernaryFilter::make('is_active')->default(true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ReplicateAction::make()
                    ->beforeReplicaSaved(function (EbookPack $replica) {
                        $replica->name = $replica->name . ' (Copy)';
                        $replica->is_active = false;
                    }),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->emptyStateHeading('No e-Book packs yet')
            ->emptyStateDescription('Create one per (Unit + Grade) cohort. Make sure the e-Book Platforms registry has at least one entry first.')
            ->emptyStateIcon('heroicon-o-rectangle-stack');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEbookPacks::route('/'),
            'create' => Pages\CreateEbookPack::route('/create'),
            'edit'   => Pages\EditEbookPack::route('/{record}/edit'),
        ];
    }
}
