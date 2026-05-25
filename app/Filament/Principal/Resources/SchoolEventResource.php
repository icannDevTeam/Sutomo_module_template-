<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\SchoolEventResource\Pages;
use App\Models\SchoolEvent;
use App\Support\CsvExporter;
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
            Forms\Components\Select::make('campus')->label('Unit')->options(\App\Support\SchoolDirectory::unitOptions())->required(),
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
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')->icon('heroicon-o-eye')->color('gray')
                    ->modalHeading(fn ($record) => 'Event · ' . $record->code)
                    ->modalWidth('3xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn ($record) => view('filament.principal.event.event-detail', ['record' => $record])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('exportCsv')
                    ->label('Export CSV')->icon('heroicon-o-arrow-down-tray')->color('gray')
                    ->action(fn ($records) => CsvExporter::download(
                        $records,
                        [
                            'Code'         => 'code',
                            'Title'        => 'title',
                            'Category'     => fn ($r) => SchoolEvent::CATEGORIES[$r->category] ?? $r->category,
                            'Unit'         => 'campus',
                            'Starts'       => fn ($r) => optional($r->starts_at)->format('Y-m-d'),
                            'Ends'         => fn ($r) => optional($r->ends_at)->format('Y-m-d'),
                            'PIC'          => 'pic',
                            'Participants' => 'participants',
                            'Status'       => fn ($r) => SchoolEvent::STATUSES[$r->status] ?? $r->status,
                        ],
                        CsvExporter::filename('school-events'),
                    )),
                Tables\Actions\DeleteBulkAction::make(),
            ]);
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
