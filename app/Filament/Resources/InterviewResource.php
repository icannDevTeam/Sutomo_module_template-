<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InterviewResource\Pages;
use App\Models\Interview;
use App\Support\CsvExporter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InterviewResource extends Resource
{
    protected static ?string $model = Interview::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Hiring';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->required(),
            Forms\Components\Select::make('candidate_id')->relationship('candidate', 'name')->searchable()->required(),
            Forms\Components\DatePicker::make('scheduled_date')->required(),
            Forms\Components\TimePicker::make('scheduled_time')->required(),
            Forms\Components\TextInput::make('room'),
            Forms\Components\TextInput::make('type')->default('Panel Interview'),
            Forms\Components\Select::make('status')->options([
                'scheduled' => 'Scheduled', 'completed' => 'Completed', 'cancelled' => 'Cancelled',
            ])->required()->default('scheduled'),
            Forms\Components\TagsInput::make('panel')->columnSpanFull(),
            Forms\Components\TextInput::make('recommendation'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->fontFamily('mono'),
                Tables\Columns\TextColumn::make('candidate.name')->searchable()
                    ->description(fn ($record) => $record->candidate?->vacancy?->title),
                Tables\Columns\TextColumn::make('scheduled_date')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('scheduled_time'),
                Tables\Columns\TextColumn::make('room'),
                Tables\Columns\TextColumn::make('type')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'scheduled' => 'warning', 'completed' => 'success', 'cancelled' => 'danger',
                }),
                Tables\Columns\TextColumn::make('recommendation')->badge()->color('success'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'scheduled' => 'Scheduled', 'completed' => 'Completed', 'cancelled' => 'Cancelled',
                ]),
            ])
            ->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([
                Tables\Actions\BulkAction::make('exportCsv')
                    ->label('Export selected (CSV)')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('gray')
                    ->deselectRecordsAfterCompletion()
                    ->action(fn ($records) => CsvExporter::download($records, static::csvColumns(), CsvExporter::filename('interviews'))),
                Tables\Actions\DeleteBulkAction::make(),
            ])])
            ->defaultSort('scheduled_date', 'desc');
    }

    public static function csvColumns(): array
    {
        return [
            'Code'             => 'code',
            'Candidate'        => fn ($r) => $r->candidate?->name,
            'Candidate code'   => fn ($r) => $r->candidate?->code,
            'Vacancy'          => fn ($r) => $r->candidate?->vacancy?->title,
            'Scheduled date'   => 'scheduled_date',
            'Scheduled time'   => 'scheduled_time',
            'Room'             => 'room',
            'Type'             => 'type',
            'Status'           => 'status',
            'Panel'            => 'panel',
            'Recommendation'   => 'recommendation',
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInterviews::route('/'),
            'create' => Pages\CreateInterview::route('/create'),
            'edit'   => Pages\EditInterview::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['code', 'room', 'type', 'candidate.name'];
    }

    public static function getGlobalSearchResultTitle($record): string
    {
        return ($record->candidate?->name ?? 'Interview') . ' · ' . ($record->scheduled_date?->format('d M') ?? '');
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Time'   => $record->scheduled_time,
            'Room'   => $record->room ?? '—',
            'Status' => ucfirst((string) $record->status),
        ];
    }
}
