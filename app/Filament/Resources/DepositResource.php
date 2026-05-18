<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepositResource\Pages;
use App\Models\Deposit;
use App\Support\CsvExporter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DepositResource extends Resource
{
    protected static ?string $model = Deposit::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('candidate_id')->relationship('candidate', 'name')->searchable()->required(),
            Forms\Components\TextInput::make('amount')->numeric()->prefix('Rp')->required()->default(5000000),
            Forms\Components\Select::make('status')->options([
                'pending' => 'Pending', 'verified' => 'Verified', 'refunded' => 'Refunded',
            ])->required()->default('pending'),
            Forms\Components\DatePicker::make('paid_at'),
            Forms\Components\DatePicker::make('due_date'),
            Forms\Components\Toggle::make('refund_eligible'),
            Forms\Components\TextInput::make('receipt'),
            Forms\Components\TextInput::make('bank')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('candidate.code')->fontFamily('mono')->label('Code'),
                Tables\Columns\TextColumn::make('candidate.name')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('amount')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'verified' => 'success', 'pending' => 'warning', 'refunded' => 'gray',
                }),
                Tables\Columns\TextColumn::make('paid_at')->date('d M Y'),
                Tables\Columns\TextColumn::make('due_date')->date('d M Y'),
                Tables\Columns\IconColumn::make('refund_eligible')->boolean()->label('Refundable'),
                Tables\Columns\TextColumn::make('bank'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending' => 'Pending', 'verified' => 'Verified', 'refunded' => 'Refunded',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('downloadReceipt')
                    ->label('Receipt')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('gray')
                    ->visible(fn (Deposit $record) => filled($record->receipt))
                    ->action(function (Deposit $record) {
                        $body = "YAYASAN SUTOMO — DEPOSIT RECEIPT\n" .
                            str_repeat('=', 40) . "\n\n" .
                            "Receipt no : {$record->receipt}\n" .
                            "Candidate  : " . ($record->candidate?->name ?? '—') . "\n" .
                            "Code       : " . ($record->candidate?->code ?? '—') . "\n" .
                            "Amount     : Rp " . number_format((int) $record->amount, 0, ',', '.') . "\n" .
                            "Status     : " . ucfirst((string) $record->status) . "\n" .
                            "Paid at    : " . ($record->paid_at?->format('d M Y') ?? '—') . "\n" .
                            "Bank       : " . ($record->bank ?? '—') . "\n\n" .
                            "Generated  : " . now()->format('d M Y H:i') . "\n";
                        return response()->streamDownload(
                            fn () => print($body),
                            ($record->receipt ?: 'receipt-' . $record->id) . '.txt',
                            ['Content-Type' => 'text/plain; charset=UTF-8'],
                        );
                    }),
                Tables\Actions\Action::make('markVerified')
                    ->label('Verify')
                    ->icon('heroicon-m-check-badge')
                    ->color('success')
                    ->visible(fn (Deposit $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (Deposit $record) {
                        $record->update(['status' => 'verified', 'paid_at' => $record->paid_at ?? now()->toDateString()]);
                        Notification::make()->title('Deposit verified')->success()->send();
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
                    ->action(fn ($records) => CsvExporter::download($records, static::csvColumns(), CsvExporter::filename('deposits'))),
                Tables\Actions\DeleteBulkAction::make(),
            ])])
            ->defaultSort('paid_at', 'desc');
    }

    public static function csvColumns(): array
    {
        return [
            'Candidate code'  => fn ($r) => $r->candidate?->code,
            'Candidate name'  => fn ($r) => $r->candidate?->name,
            'Amount (IDR)'    => 'amount',
            'Status'          => 'status',
            'Paid at'         => 'paid_at',
            'Due date'        => 'due_date',
            'Refund eligible' => 'refund_eligible',
            'Receipt'         => 'receipt',
            'Bank'            => 'bank',
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDeposits::route('/'),
            'create' => Pages\CreateDeposit::route('/create'),
            'edit'   => Pages\EditDeposit::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['receipt', 'bank', 'candidate.name', 'candidate.code'];
    }

    public static function getGlobalSearchResultTitle($record): string
    {
        return ($record->candidate?->name ?? 'Deposit') . ' — Rp ' . number_format((int) $record->amount, 0, ',', '.');
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Status' => ucfirst((string) $record->status),
            'Bank'   => $record->bank ?? '—',
        ];
    }
}
