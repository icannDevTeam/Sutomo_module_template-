<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\LeaveTypeResource\Pages;
use App\Models\LeaveType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeaveTypeResource extends Resource
{
    protected static ?string $model = LeaveType::class;
    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?string $navigationGroup = 'Leave & Substitution';
    protected static ?string $navigationLabel = 'Leave Types';
    protected static ?string $modelLabel = 'Leave Type';
    protected static ?int $navigationSort = 89;
    protected static ?string $slug = 'leave-types';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('label')
                    ->required()
                    ->maxLength(64)
                    ->placeholder('e.g. Brief Absence')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if (! $get('key') && $state) {
                            $set('key', \Illuminate\Support\Str::slug($state, '_'));
                        }
                    }),

                Forms\Components\TextInput::make('key')
                    ->required()
                    ->maxLength(32)
                    ->disabledOn('edit')
                    ->dehydrated()
                    ->helperText('Internal slug. Cannot be changed after creation.')
                    ->placeholder('brief_absence'),
            ]),

            Forms\Components\Textarea::make('description')
                ->rows(2)
                ->maxLength(500)
                ->placeholder('When should this leave type be used?'),

            Forms\Components\Section::make('Quota behaviour')
                ->description('Decide whether this absence type counts against the teacher\'s leave quota.')
                ->schema([
                    Forms\Components\Toggle::make('affects_quota')
                        ->label('Counts against quota')
                        ->helperText('When ON, days approved under this type are deducted from the teacher\'s quota. Turn OFF for school-assigned duties, brief absences, bereavement, etc.')
                        ->default(true)
                        ->onIcon('heroicon-m-check')
                        ->offIcon('heroicon-m-x-mark')
                        ->onColor('danger')
                        ->offColor('success'),
                ])->compact(),

            Forms\Components\Section::make('Substitute coverage')
                ->description('Decide whether a leave of this type requires a substitute teacher to cover the absent teacher\'s scheduled classes.')
                ->schema([
                    Forms\Components\Toggle::make('requires_substitute')
                        ->label('Requires a substitute')
                        ->helperText('When ON, the leave-submission flow shows the substitute picker / auto-search. Turn OFF for absence types like "Brief Absence" or "Assigned Work" where no class cover is needed.')
                        ->default(true)
                        ->onIcon('heroicon-m-check')
                        ->offIcon('heroicon-m-x-mark')
                        ->onColor('success')
                        ->offColor('gray'),
                ])->compact(),

            Forms\Components\Grid::make(4)->schema([
                Forms\Components\Select::make('color')
                    ->options(LeaveType::COLOR_OPTIONS)
                    ->default('gray')
                    ->required(),
                Forms\Components\TextInput::make('icon')
                    ->placeholder('heroicon-o-calendar')
                    ->helperText('Heroicon name (optional).')
                    ->maxLength(64),
                Forms\Components\TextInput::make('max_days')
                    ->numeric()
                    ->minValue(1)
                    ->nullable()
                    ->helperText('Optional cap for this leave type (calendar days). Leave empty for no limit.'),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
            ]),

            Forms\Components\Toggle::make('is_active')
                ->label('Active')
                ->helperText('Inactive types stay in the database but disappear from the leave-submission form.')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (LeaveType $r) => $r->description),
                Tables\Columns\TextColumn::make('key')
                    ->fontFamily('mono')
                    ->size('sm')
                    ->color('gray'),
                Tables\Columns\IconColumn::make('affects_quota')
                    ->label('Counts vs quota')
                    ->boolean()
                    ->trueIcon('heroicon-m-check-circle')
                    ->falseIcon('heroicon-m-minus-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->tooltip(fn (LeaveType $r) => $r->affects_quota
                        ? 'Days deducted from teacher quota'
                        : 'Does NOT touch teacher quota'),
                Tables\Columns\IconColumn::make('requires_substitute')
                    ->label('Needs sub')
                    ->boolean()
                    ->trueIcon('heroicon-m-user-plus')
                    ->falseIcon('heroicon-m-minus-circle')
                    ->trueColor('info')
                    ->falseColor('gray')
                    ->tooltip(fn (LeaveType $r) => $r->requires_substitute
                        ? 'Triggers substitute picker / auto-search'
                        : 'No class cover needed'),
                Tables\Columns\TextColumn::make('color')
                    ->badge()
                    ->color(fn ($state) => $state),
                Tables\Columns\TextColumn::make('max_days')
                    ->label('Max days')
                    ->formatStateUsing(fn ($state) => $state ?: '—')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('affects_quota')
                    ->label('Affects quota'),
                Tables\Filters\TernaryFilter::make('requires_substitute')
                    ->label('Requires substitute'),
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLeaveTypes::route('/'),
            'create' => Pages\CreateLeaveType::route('/create'),
            'edit'   => Pages\EditLeaveType::route('/{record}/edit'),
        ];
    }
}
