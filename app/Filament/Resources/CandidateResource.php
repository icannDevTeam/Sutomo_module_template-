<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CandidateResource\Pages;
use App\Models\AuditLog;
use App\Models\Candidate;
use App\Support\CsvExporter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CandidateResource extends Resource
{
    protected static ?string $model = Candidate::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Hiring';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationLabel = 'Candidates';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identity')->columns(2)->schema([
                Forms\Components\TextInput::make('code')->required(),
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\Select::make('gender')->options(['M' => 'Male', 'F' => 'Female']),
                Forms\Components\TextInput::make('age')->numeric(),
                Forms\Components\TextInput::make('email')->email(),
                Forms\Components\TextInput::make('phone')->tel(),
                Forms\Components\TextInput::make('city'),
                Forms\Components\TextInput::make('education'),
            ]),
            Forms\Components\Section::make('Hiring')->columns(2)->schema([
                Forms\Components\Select::make('vacancy_id')->relationship('vacancy', 'title')->searchable()->preload(),
                Forms\Components\Select::make('stage')->options(Candidate::STAGES)->required()->default('applied'),
                Forms\Components\Select::make('priority')->options([
                    'low' => 'Low', 'normal' => 'Normal', 'high' => 'High',
                ])->default('normal'),
                Forms\Components\DatePicker::make('applied_at'),
                Forms\Components\TextInput::make('years')->numeric()->default(0),
                Forms\Components\TagsInput::make('subjects'),
            ]),
            Forms\Components\Section::make('Scores')->columns(3)->schema([
                Forms\Components\TextInput::make('score_written')->numeric()->suffix('/100'),
                Forms\Components\TextInput::make('score_interview')->numeric()->suffix('/100'),
                Forms\Components\TextInput::make('score_micro')->numeric()->suffix('/100'),
            ]),
            Forms\Components\Section::make('Pipeline Meta')->collapsed()->schema([
                Forms\Components\KeyValue::make('meta')->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn ($record) => static::getUrl('view', ['record' => $record]))
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable()->sortable()->fontFamily('mono'),
                Tables\Columns\TextColumn::make('name')->searchable()->weight('bold')
                    ->description(fn ($record) => $record->vacancy?->title),
                Tables\Columns\TextColumn::make('stage')->badge()
                    ->formatStateUsing(fn ($state) => Candidate::STAGES[$state] ?? $state)
                    ->color(fn ($state) => Candidate::STAGE_COLORS[$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('priority')->badge()->color(fn ($state) => match ($state) {
                    'high' => 'danger', 'normal' => 'gray', 'low' => 'info', default => 'gray',
                }),
                Tables\Columns\TextColumn::make('score_written')->label('W')->numeric()->alignCenter(),
                Tables\Columns\TextColumn::make('score_interview')->label('I')->numeric()->alignCenter(),
                Tables\Columns\TextColumn::make('score_micro')->label('μ')->numeric()->alignCenter(),
                Tables\Columns\TextColumn::make('applied_at')->date('d M Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stage')->options(Candidate::STAGES),
                Tables\Filters\SelectFilter::make('priority')->options([
                    'high' => 'High', 'normal' => 'Normal', 'low' => 'Low',
                ]),
                Tables\Filters\SelectFilter::make('vacancy_id')->relationship('vacancy', 'title')->label('Vacancy'),
            ])
            ->actions([
                Tables\Actions\Action::make('advance')
                    ->label('Advance')
                    ->icon('heroicon-m-arrow-right')
                    ->color('primary')
                    ->size('xs')
                    ->action(function (Candidate $record) {
                        $order = ['applied','screening','written','interview','psycho','medical','yayasan','opl','active'];
                        $idx = array_search($record->stage, $order);
                        if ($idx === false || $idx >= count($order) - 1) {
                            Notification::make()->title('Already at final stage')->warning()->send();
                            return;
                        }
                        $from = $record->stage;
                        $to = $order[$idx + 1];
                        $record->update(['stage' => $to]);
                        AuditLog::create([
                            'occurred_at' => now(),
                            'user_name'   => auth()->user()?->name ?? 'System',
                            'role'        => 'HR',
                            'action'      => 'stage.move',
                            'target'      => $record->code,
                            'from_value'  => $from,
                            'to_value'    => $to,
                            'note'        => 'Advanced from candidates table.',
                        ]);
                        Notification::make()->title('Moved to ' . (Candidate::STAGES[$to] ?? $to))->success()->send();
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([
                Tables\Actions\BulkAction::make('exportCsv')
                    ->label('Export selected (CSV)')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('gray')
                    ->deselectRecordsAfterCompletion()
                    ->action(fn ($records) => CsvExporter::download($records, static::csvColumns(), CsvExporter::filename('candidates'))),
                Tables\Actions\DeleteBulkAction::make(),
            ])])
            ->defaultSort('applied_at', 'desc');
    }

    public static function csvColumns(): array
    {
        return [
            'Code'           => 'code',
            'Name'           => 'name',
            'Gender'         => 'gender',
            'Age'            => 'age',
            'Email'          => 'email',
            'Phone'          => 'phone',
            'City'           => 'city',
            'Education'      => 'education',
            'Years exp.'     => 'years',
            'Subjects'       => 'subjects',
            'Vacancy'        => fn ($r) => $r->vacancy?->title,
            'Stage'          => fn ($r) => Candidate::STAGES[$r->stage] ?? $r->stage,
            'Priority'       => 'priority',
            'Score written'  => 'score_written',
            'Score intvw'    => 'score_interview',
            'Score micro'    => 'score_micro',
            'Applied at'     => 'applied_at',
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCandidates::route('/'),
            'create' => Pages\CreateCandidate::route('/create'),
            'view'   => Pages\ViewCandidate::route('/{record}'),
            'edit'   => Pages\EditCandidate::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['code', 'name', 'email', 'phone', 'city'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Stage'   => Candidate::STAGES[$record->stage] ?? $record->stage,
            'Vacancy' => $record->vacancy?->title ?? '—',
        ];
    }
}
