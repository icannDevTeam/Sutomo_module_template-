<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\PaymentAccountResource\Pages;
use App\Models\PaymentAccount;
use App\Support\SchoolDirectory;
use App\Support\VirtualAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentAccountResource extends Resource
{
    protected static ?string $model = PaymentAccount::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Enrollment';
    protected static ?int $navigationSort = 7;
    protected static ?string $label = 'VA Account';
    protected static ?string $pluralLabel = 'VA Accounts';
    protected static ?string $navigationLabel = 'VA Accounts';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Virtual Account scheme')
                ->description(new \Illuminate\Support\HtmlString(
                    '<div class="text-xs leading-relaxed text-gray-600">'
                    .'<strong>VA format:</strong> <code>14339</code> + <code>unit</code> + <code>purpose</code> + <code>ref</code>'
                    .'<br><strong>Unit digit:</strong> 1 SMA · 2 SMP · 3 SD · 4 TK · 5 Playgroup · 6 Pre-Nursery'
                    .'<br><strong>Purpose digit:</strong> 0 Tuition · 1 Admin · 2 Textbooks · 3 Dev Fee · 4 Enrollment'
                    .'<br><strong>Ref tail:</strong> NIS for tuition · registration number for enrollment'
                    .'<br><em>Example:</em> <code>14339&nbsp;1&nbsp;4&nbsp;2610011</code> = SMA enrollment fee for registration #2610011'
                    .'<br><span class="text-amber-700">Note: the actual VA account number is provided by the Unit head (internal).</span>'
                    .'</div>'
                ))
                ->collapsible()
                ->collapsed()
                ->schema([]),

            Forms\Components\Section::make('Account scope')->columns(3)->schema([
                Forms\Components\Select::make('campus')
                    ->options(SchoolDirectory::campusOptions())
                    ->required(),
                Forms\Components\Select::make('unit')
                    ->options(PaymentAccount::UNITS)
                    ->required()
                    ->live()
                    ->helperText('Drives the unit digit of the VA prefix.'),
                Forms\Components\Select::make('purpose')
                    ->options(PaymentAccount::PURPOSES)
                    ->default('books')
                    ->required()
                    ->live()
                    ->helperText('Drives the purpose digit of the VA prefix.'),
                Forms\Components\Toggle::make('is_active')->default(true)
                    ->helperText('Only active accounts appear in the onboarding picker.'),
            ]),

            Forms\Components\Section::make('Bank details')->columns(2)->schema([
                Forms\Components\TextInput::make('bank_name')->required()
                    ->default('BCA')
                    ->placeholder('BCA / Mandiri / BNI'),
                Forms\Components\TextInput::make('account_no')
                    ->label('Account number / VA')->required()
                    ->placeholder('14339142610011')
                    ->helperText('VA account number is provided by the Unit head.'),
                Forms\Components\TextInput::make('account_name')
                    ->required()->columnSpanFull()
                    ->placeholder('Yayasan Sutomo — Buku Pelajaran'),
                Forms\Components\Placeholder::make('va_prefix_preview')
                    ->label('Computed VA prefix')
                    ->columnSpanFull()
                    ->content(function (Get $get) {
                        $u = VirtualAccount::unitDigit($get('unit'));
                        $p = VirtualAccount::purposeDigit($get('purpose'));
                        if (! $u || ! $p) {
                            return new \Illuminate\Support\HtmlString(
                                '<span class="text-gray-500">Pick a unit + purpose to see the canonical prefix.</span>'
                            );
                        }
                        $prefix = VirtualAccount::BANK_PREFIX . ' ' . $u . ' ' . $p;
                        return new \Illuminate\Support\HtmlString(
                            '<code class="px-2 py-1 rounded bg-indigo-50 text-indigo-700 font-mono text-sm">'
                            . e($prefix) . ' &lt;ref&gt;</code> '
                            . '<span class="text-xs text-gray-500 ml-2">'
                            . e(PaymentAccount::UNITS[$get('unit')] ?? '') . ' · '
                            . e(PaymentAccount::PURPOSES[$get('purpose')] ?? '') . '</span>'
                        );
                    }),
                Forms\Components\TextInput::make('va_prefix')
                    ->label('Override VA prefix (optional)')
                    ->placeholder('Leave blank to use the computed prefix')
                    ->helperText('Only fill this if the Unit head has issued a non-standard prefix.'),
            ]),
            Forms\Components\Textarea::make('notes')->rows(2)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('campus')->badge()
                    ->formatStateUsing(fn ($state) => SchoolDirectory::campusLabel($state) ?? $state)
                    ->color('info'),
                Tables\Columns\TextColumn::make('unit')->badge()
                    ->formatStateUsing(fn ($state) => PaymentAccount::UNITS[$state] ?? '—')
                    ->color('gray'),
                Tables\Columns\TextColumn::make('purpose')->badge()
                    ->formatStateUsing(fn ($state) => PaymentAccount::PURPOSES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'books' => 'warning',
                        'tuition' => 'success',
                        'enrollment' => 'info',
                        'dev_fee' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('va_prefix_computed')
                    ->label('VA prefix')
                    ->getStateUsing(fn (PaymentAccount $r) => $r->va_prefix_computed ?? '—')
                    ->fontFamily('mono')->copyable(),
                Tables\Columns\TextColumn::make('bank_name')->weight('bold'),
                Tables\Columns\TextColumn::make('account_no')->copyable()->copyMessage('Copied'),
                Tables\Columns\TextColumn::make('account_name')->wrap(),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
            ])
            ->defaultSort('campus')
            ->filters([
                Tables\Filters\SelectFilter::make('campus')->options(SchoolDirectory::campusOptions()),
                Tables\Filters\SelectFilter::make('unit')->options(PaymentAccount::UNITS),
                Tables\Filters\SelectFilter::make('purpose')->options(PaymentAccount::PURPOSES),
                Tables\Filters\TernaryFilter::make('is_active')->default(true),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->emptyStateHeading('No VA accounts')
            ->emptyStateDescription('Add at least one VA account per unit + purpose so onboarding payments can be routed.')
            ->emptyStateIcon('heroicon-o-credit-card');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPaymentAccounts::route('/'),
            'create' => Pages\CreatePaymentAccount::route('/create'),
            'edit'   => Pages\EditPaymentAccount::route('/{record}/edit'),
        ];
    }
}
