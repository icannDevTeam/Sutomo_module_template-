<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VacancyResource\Pages;
use App\Models\Vacancy;
use App\Support\CsvExporter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VacancyResource extends Resource
{
    protected static ?string $model = Vacancy::class;
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'Hiring';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Posting')->columns(2)->schema([
                Forms\Components\TextInput::make('code')->required()->placeholder('V-2026-014'),
                Forms\Components\TextInput::make('title')->required()->columnSpan(1),
                Forms\Components\TextInput::make('dept')->label('Department'),
                Forms\Components\Select::make('campus')->options([
                    'sd' => 'SD (Primary)', 'smp' => 'SMP (Junior High)',
                    'sma' => 'SMA (Senior High)', 'int' => 'International',
                ])->required(),
                Forms\Components\Select::make('type')->options([
                    'Full-time' => 'Full-time', 'Part-time' => 'Part-time', 'Contract' => 'Contract',
                ])->default('Full-time'),
                Forms\Components\TextInput::make('level'),
                Forms\Components\TextInput::make('openings')->numeric()->default(1),
                Forms\Components\TextInput::make('applicants')->numeric()->default(0),
                Forms\Components\DatePicker::make('posted_at')->label('Posted'),
                Forms\Components\DatePicker::make('closes_at')->label('Closes'),
                Forms\Components\Select::make('status')->options([
                    'open' => 'Open', 'closing' => 'Closing Soon', 'closed' => 'Closed',
                ])->required()->default('open'),
                Forms\Components\Toggle::make('featured'),
            ]),
            Forms\Components\Textarea::make('summary')->rows(4)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable()->sortable()->fontFamily('mono'),
                Tables\Columns\TextColumn::make('title')->searchable()->weight('bold')
                    ->description(fn ($record) => $record->dept . ' · ' . strtoupper($record->campus)),
                Tables\Columns\TextColumn::make('type')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('applicants')->numeric()->sortable()->alignCenter(),
                Tables\Columns\TextColumn::make('openings')->numeric()->sortable()->alignCenter(),
                Tables\Columns\TextColumn::make('closes_at')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'open' => 'success', 'closing' => 'warning', 'closed' => 'gray',
                }),
                Tables\Columns\IconColumn::make('featured')->boolean()->label('★'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'open' => 'Open', 'closing' => 'Closing', 'closed' => 'Closed',
                ]),
                Tables\Filters\SelectFilter::make('campus')->options([
                    'sd' => 'SD', 'smp' => 'SMP', 'sma' => 'SMA', 'int' => 'International',
                ]),
            ])
            ->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([
                Tables\Actions\BulkAction::make('exportCsv')
                    ->label('Export selected (CSV)')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('gray')
                    ->deselectRecordsAfterCompletion()
                    ->action(fn ($records) => CsvExporter::download($records, static::csvColumns(), CsvExporter::filename('vacancies'))),
                Tables\Actions\DeleteBulkAction::make(),
            ])])
            ->defaultSort('posted_at', 'desc');
    }

    public static function csvColumns(): array
    {
        return [
            'Code'       => 'code',
            'Title'      => 'title',
            'Department' => 'dept',
            'Campus'     => fn ($r) => strtoupper((string) $r->campus),
            'Type'       => 'type',
            'Level'      => 'level',
            'Openings'   => 'openings',
            'Applicants' => 'applicants',
            'Posted'     => 'posted_at',
            'Closes'     => 'closes_at',
            'Status'     => 'status',
            'Featured'   => 'featured',
            'Summary'    => 'summary',
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVacancies::route('/'),
            'create' => Pages\CreateVacancy::route('/create'),
            'edit'   => Pages\EditVacancy::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['code', 'title', 'dept'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Department' => $record->dept,
            'Campus'     => strtoupper((string) $record->campus),
            'Status'     => ucfirst((string) $record->status),
        ];
    }
}
