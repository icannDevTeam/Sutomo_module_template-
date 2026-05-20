<?php

namespace App\Filament\Principal\Resources;

use App\Filament\Principal\Resources\ApplicationResource\Pages;
use App\Models\Application;
use App\Support\SchoolDirectory;
use App\Models\EnrollmentPeriod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Enrollment';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'name';

    /* =========================================================
       FORM (intake wizard — mirrors the PMB paper form)
       ========================================================= */
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Wizard::make([
                Forms\Components\Wizard\Step::make('A. Data Siswa')
                    ->icon('heroicon-o-user')->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('code')->label('Application Code')->placeholder('Auto on save')->maxLength(20),
                        Forms\Components\TextInput::make('nisn')->label('NISN (Nomor Induk Siswa Nasional)')->maxLength(20),
                        Forms\Components\TextInput::make('name')->label('Nama Lengkap (sesuai Akta Lahir)')->required()->columnSpanFull(),
                        Forms\Components\Select::make('gender')->label('Jenis Kelamin')
                            ->options(['M' => 'Laki-laki', 'F' => 'Perempuan'])->required(),
                        Forms\Components\TextInput::make('birthplace')->label('Tempat Lahir'),
                        Forms\Components\DatePicker::make('dob')->label('Tanggal Lahir')->required(),
                        Forms\Components\Select::make('religion')->label('Agama')
                            ->options(['Islam' => 'Islam', 'Kristen' => 'Kristen', 'Katolik' => 'Katolik', 'Buddha' => 'Buddha', 'Hindu' => 'Hindu', 'Khonghucu' => 'Khonghucu']),
                        Forms\Components\TextInput::make('ethnicity')->label('Suku'),
                        Forms\Components\TextInput::make('address')->label('Alamat')->columnSpanFull(),
                        Forms\Components\TextInput::make('city')->label('Kabupaten / Kota'),
                        Forms\Components\Select::make('orphan_status')->label('Anak Yatim/Piatu')
                            ->options(Application::ORPHAN_STATUSES)->default('none'),
                        Forms\Components\TextInput::make('current_school')->label('Nama Sekolah Asal')->columnSpanFull(),

                        Forms\Components\Section::make('Status Siswa')
                            ->description('Tentukan tipe pelamar dan apakah anak guru Sutomo.')
                            ->columns(3)
                            ->schema([
                                Forms\Components\Select::make('applicant_type')->label('Applicant Type')
                                    ->options(Application::APPLICANT_TYPES)
                                    ->default('new')->required()->live(),
                                Forms\Components\Select::make('existing_unit')->label('Current Sutomo Unit')
                                    ->options(SchoolDirectory::unitOptions())
                                    ->visible(fn (Forms\Get $get) => in_array($get('applicant_type'), ['existing', 'returning'], true)),
                                Forms\Components\Toggle::make('is_teacher_child')
                                    ->label('★ Child of a Sutomo teacher')
                                    ->inline(false)->columnStart(3),
                            ]),
                    ]),

                Forms\Components\Wizard\Step::make('B. Target Placement')
                    ->icon('heroicon-o-academic-cap')->columns(2)
                    ->schema([
                        Forms\Components\Select::make('enrollment_period_id')->label('Enrollment Period')
                            ->options(EnrollmentPeriod::query()->orderByDesc('opens_at')->pluck('name', 'id'))
                            ->searchable(),
                        Forms\Components\Select::make('campus')->label('Unit (Level)')
                            ->options(SchoolDirectory::unitOptions())
                            ->required()->live()
                            ->helperText('Educational unit run by a Unit Head.'),
                        Forms\Components\Select::make('unit')->label('Physical Campus')
                            ->options(SchoolDirectory::campusOptions())
                            ->required()
                            ->helperText('School & physical campus the student attends.'),
                        Forms\Components\TextInput::make('grade')->label('Kelas (Grade)')->required(),
                        Forms\Components\Select::make('stream')->label('Jurusan (Stream)')
                            ->options(['ipa' => 'IPA', 'ips' => 'IPS', 'umum' => 'Umum'])
                            ->visible(fn (Forms\Get $get) => $get('campus') === 'sma'),
                    ]),

                Forms\Components\Wizard\Step::make('C. Wali / Guardian')
                    ->icon('heroicon-o-users')->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('parent_name')->label('Nama Orang Tua / Wali')->required(),
                        Forms\Components\TextInput::make('parent_phone')->label('Nomor Handphone')->tel()->required(),
                        Forms\Components\TextInput::make('parent_whatsapp')->label('Nomor WhatsApp')->tel()
                            ->helperText('Leave blank if same as phone'),
                        Forms\Components\TextInput::make('parent_email')->label('Email')->email()->required(),
                        Forms\Components\TextInput::make('parent_occupation')->label('Pekerjaan'),
                    ]),

                Forms\Components\Wizard\Step::make('D. Pembayaran')
                    ->icon('heroicon-o-banknotes')->columns(2)
                    ->schema([
                        Forms\Components\Placeholder::make('fee')->label('Application Fee')
                            ->content('Rp 300.000 — non-refundable'),
                        Forms\Components\Select::make('payment_method')->label('Payment Method')
                            ->options(Application::PAYMENT_METHODS),
                        Forms\Components\TextInput::make('payment_amount')->label('Amount Paid (Rp)')->numeric()->default(300000),
                        Forms\Components\DateTimePicker::make('payment_paid_at')->label('Paid At'),
                        Forms\Components\TextInput::make('invoice_no')->label('Invoice No')->placeholder('Auto on save'),
                        Forms\Components\Select::make('payment_status')
                            ->options(['pending' => 'Pending', 'paid' => 'Paid', 'refunded' => 'Refunded'])
                            ->default('pending'),
                        Forms\Components\FileUpload::make('receipt_file')->label('Upload Receipt / Bukti Transfer')
                            ->disk('public')->directory('receipts')
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->maxSize(4096)->columnSpanFull(),
                    ]),

                Forms\Components\Wizard\Step::make('E. Documents')
                    ->icon('heroicon-o-paper-clip')
                    ->schema([
                        Forms\Components\FileUpload::make('meta.documents')
                            ->label('Attached Documents')
                            ->helperText('Akta Lahir, KK, Rapor, foto, surat keterangan, dll. Up to 10 files.')
                            ->disk('public')->directory('applications/docs')
                            ->multiple()->reorderable()
                            ->maxFiles(10)->maxSize(8192)
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Wizard\Step::make('F. Pipeline')
                    ->icon('heroicon-o-flag')->columns(2)
                    ->schema([
                        Forms\Components\Select::make('status')->options(Application::STATUSES)->default('submitted')->required(),
                        Forms\Components\DatePicker::make('applied_at')->default(now()),
                    ]),
            ])->columnSpanFull()->skippable(),
        ]);
    }

    /* =========================================================
       INFOLIST (rich dossier — for the View page)
       ========================================================= */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make()
                ->schema([
                    Infolists\Components\Grid::make(['default' => 1, 'md' => 4])->schema([
                        Infolists\Components\TextEntry::make('code')->label('Application code')
                            ->badge()->color('primary')->copyable(),
                        Infolists\Components\TextEntry::make('name')->label('Full name')
                            ->weight('bold')->size('lg')
                            ->formatStateUsing(function ($state, $record) {
                                $extra = $record->is_teacher_child ? '  ·  ★ Teacher child' : '';
                                return $state . $extra;
                            }),
                        Infolists\Components\TextEntry::make('applicant_type')->label('Applicant type')
                            ->badge()
                            ->color(fn ($state) => $state === 'new' ? 'success' : 'info')
                            ->formatStateUsing(fn ($state) => Application::applicantTypeLabel($state)),
                        Infolists\Components\TextEntry::make('status')->label('Pipeline status')
                            ->badge()
                            ->color(fn ($state) => Application::STATUS_COLORS[$state] ?? 'gray')
                            ->formatStateUsing(fn ($state) => Application::STATUSES[$state] ?? $state),
                    ]),
                ])->compact(),

            Infolists\Components\Section::make('A. Data Siswa')
                ->icon('heroicon-o-identification')
                ->collapsible()->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('nisn')->label('NISN'),
                    Infolists\Components\TextEntry::make('gender')
                        ->formatStateUsing(fn ($state) => $state === 'M' ? 'Laki-laki' : 'Perempuan'),
                    Infolists\Components\TextEntry::make('dob')->label('Tanggal Lahir')->date(),
                    Infolists\Components\TextEntry::make('birthplace')->label('Tempat Lahir'),
                    Infolists\Components\TextEntry::make('religion')->label('Agama'),
                    Infolists\Components\TextEntry::make('ethnicity')->label('Suku'),
                    Infolists\Components\TextEntry::make('address')->label('Alamat')->columnSpan(2),
                    Infolists\Components\TextEntry::make('city')->label('Kabupaten / Kota'),
                    Infolists\Components\TextEntry::make('current_school')->label('Sekolah Asal')->columnSpan(2),
                    Infolists\Components\TextEntry::make('orphan_status')->label('Yatim/Piatu')->badge()->color('gray')
                        ->formatStateUsing(fn ($state) => Application::ORPHAN_STATUSES[$state] ?? '—'),
                    Infolists\Components\TextEntry::make('existing_unit')->label('Current Sutomo Unit')
                        ->badge()->color('warning')
                        ->visible(fn ($record) => $record->applicant_type !== 'new'),
                ]),

            Infolists\Components\Section::make('B. Target Placement')
                ->icon('heroicon-o-academic-cap')
                ->collapsible()->columns(4)
                ->schema([
                    Infolists\Components\TextEntry::make('enrollmentPeriod.name')->label('Period')->badge()->color('info'),
                    Infolists\Components\TextEntry::make('campus')->label('Unit')->badge()->color('primary')
                        ->formatStateUsing(fn ($state) => SchoolDirectory::unitLabel($state) ?? '—'),
                    Infolists\Components\TextEntry::make('unit')->label('Campus')
                        ->formatStateUsing(fn ($state) => SchoolDirectory::campusLabel($state) ?? $state ?? '—'),
                    Infolists\Components\TextEntry::make('grade')->label('Kelas'),
                    Infolists\Components\TextEntry::make('stream')->label('Jurusan')
                        ->badge()->color('gray')
                        ->formatStateUsing(fn ($state) => $state ? strtoupper($state) : '—'),
                ]),

            Infolists\Components\Section::make('C. Wali / Guardian')
                ->icon('heroicon-o-users')
                ->collapsible()->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('parent_name')->label('Nama Wali'),
                    Infolists\Components\TextEntry::make('parent_phone')->label('Handphone')->copyable(),
                    Infolists\Components\TextEntry::make('parent_whatsapp')->label('WhatsApp')->copyable(),
                    Infolists\Components\TextEntry::make('parent_email')->label('Email')->copyable(),
                    Infolists\Components\TextEntry::make('parent_occupation')->label('Pekerjaan'),
                ]),

            Infolists\Components\Section::make('D. Payment')
                ->icon('heroicon-o-banknotes')
                ->collapsible()->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('invoice_no')->label('Invoice No')->badge()->color('primary'),
                    Infolists\Components\TextEntry::make('va_number')->label('BCA Virtual Account')->copyable()->fontFamily('mono')
                        ->formatStateUsing(fn ($state) => $state ? \App\Support\VirtualAccount::format($state) : 'Not issued'),
                    Infolists\Components\TextEntry::make('va_expires_at')->label('VA expires')->dateTime()
                        ->color(fn ($state) => $state && \Illuminate\Support\Carbon::parse($state)->isPast() ? 'danger' : 'gray')
                        ->placeholder('—'),
                    Infolists\Components\TextEntry::make('payment_method')->badge()
                        ->formatStateUsing(fn ($state) => Application::PAYMENT_METHODS[$state] ?? '—'),
                    Infolists\Components\TextEntry::make('payment_amount')->label('Amount')->money('IDR'),
                    Infolists\Components\TextEntry::make('payment_paid_at')->label('Paid At')->dateTime(),
                    Infolists\Components\TextEntry::make('payment_status')->badge()
                        ->color(fn ($state) => $state === 'paid' ? 'success' : 'warning')
                        ->formatStateUsing(fn ($state) => ucfirst($state ?? 'pending')),
                    Infolists\Components\TextEntry::make('receipt_file')->label('Receipt')
                        ->formatStateUsing(fn ($state) => $state ? 'View receipt ↗' : 'Not uploaded')
                        ->url(fn ($state) => $state ? Storage::disk('public')->url($state) : null, true)
                        ->color(fn ($state) => $state ? 'success' : 'gray'),
                ]),

            Infolists\Components\Section::make('E. Attached Documents')
                ->icon('heroicon-o-paper-clip')
                ->collapsible()
                ->schema([
                    Infolists\Components\TextEntry::make('meta.documents')
                        ->label('')
                        ->listWithLineBreaks()
                        ->getStateUsing(fn ($record) => array_values((array) data_get($record->meta, 'documents', [])))
                        ->formatStateUsing(fn ($state) => is_string($state) ? basename($state) : '—')
                        ->url(fn ($state) => is_string($state) ? Storage::disk('public')->url($state) : null, true)
                        ->color('primary')
                        ->icon('heroicon-o-document')
                        ->visible(fn ($record) => filled(data_get($record->meta, 'documents'))),
                    Infolists\Components\TextEntry::make('empty_docs')
                        ->state('No additional documents uploaded.')
                        ->color('gray')
                        ->visible(fn ($record) => empty(data_get($record->meta, 'documents'))),
                ]),

            Infolists\Components\Section::make('F. Pipeline')
                ->icon('heroicon-o-flag')
                ->collapsible()->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('applied_at')->label('Applied')->date(),
                    Infolists\Components\TextEntry::make('submitted_at')->label('Submitted')->dateTime()->placeholder('—'),
                ]),
        ]);
    }

    /* =========================================================
       TABLE (tab-aware: Incoming vs Confirmed)
       ========================================================= */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Code')
                    ->searchable()->copyable()->fontFamily('mono')->size('sm'),

                Tables\Columns\TextColumn::make('name')->label('Applicant')
                    ->searchable()->sortable()->weight('bold')
                    ->description(function ($record) {
                        $bits = [Application::applicantTypeLabel($record->applicant_type)];
                        if ($record->is_teacher_child) $bits[] = '★ Teacher child';
                        return implode(' · ', $bits);
                    }),

                Tables\Columns\TextColumn::make('nisn')->label('NISN')
                    ->searchable()->copyable()->fontFamily('mono')->size('sm')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('campus')->label('Unit')->badge()->color('gray')
                    ->formatStateUsing(fn ($state) => SchoolDirectory::unitLabel($state) ?? '—'),

                Tables\Columns\TextColumn::make('unit')->label('Campus')->size('sm')->color('gray')
                    ->formatStateUsing(fn ($state) => SchoolDirectory::campusLabel($state) ?? $state ?? '—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('grade')->label('Grade')->alignCenter()->sortable(),

                Tables\Columns\TextColumn::make('parent_name')->label('Wali')
                    ->description(fn ($record) => $record->parent_phone)
                    ->searchable()->toggleable(),

                Tables\Columns\TextColumn::make('applied_at')->label('Applied')->date()->sortable(),

                Tables\Columns\TextColumn::make('status')->label('Status')->badge()->color('success')
                    ->formatStateUsing(fn () => 'Application submitted')
                    ->visible(fn ($livewire) => static::isDecisionView($livewire)),

                Tables\Columns\TextColumn::make('payment_status')->label('Payment')->badge()
                    ->color(fn ($state, $livewire) => static::isDecisionView($livewire)
                        ? 'success'
                        : ($state === 'paid' ? 'success' : ($state === 'refunded' ? 'gray' : 'warning')))
                    ->formatStateUsing(fn ($state, $livewire) => static::isDecisionView($livewire)
                        ? 'Paid'
                        : ucfirst($state ?? 'pending')),

                Tables\Columns\TextColumn::make('va_number')->label('VA')->fontFamily('mono')->size('sm')
                    ->copyable()->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => $state ? \App\Support\VirtualAccount::format($state) : '—')
                    ->description(fn (Application $r) => $r->va_expires_at
                        ? ($r->va_expires_at->isPast() ? 'EXPIRED' : 'expires '.$r->va_expires_at->diffForHumans())
                        : null)
                    ->color(fn (Application $r) => $r->va_expires_at?->isPast() ? 'danger' : 'gray'),

                Tables\Columns\TextColumn::make('payment_method')->label('Method')->badge()->color('gray')
                    ->formatStateUsing(fn ($state) => Application::PAYMENT_METHODS[$state] ?? '—')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('receipt_file')->label('Receipt')->boolean()
                    ->trueIcon('heroicon-o-paper-clip')->falseIcon('heroicon-o-minus')
                    ->trueColor('success')->falseColor('gray')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('enrollment_period_id')->label('Period')
                    ->options(EnrollmentPeriod::query()->orderByDesc('opens_at')->pluck('name', 'id')),
                Tables\Filters\SelectFilter::make('campus')->label('Unit')
                    ->options(SchoolDirectory::unitOptions()),
                Tables\Filters\SelectFilter::make('unit')->label('Campus')
                    ->options(SchoolDirectory::campusOptions()),
                Tables\Filters\SelectFilter::make('applicant_type')->options(Application::APPLICANT_TYPES),
                Tables\Filters\TernaryFilter::make('is_teacher_child')->label('Teacher child'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('View dossier'),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('invoice')->label('Invoice')
                        ->icon('heroicon-o-document-currency-dollar')->color('primary')
                        ->url(fn (Application $r) => \App\Filament\Principal\Pages\ApplicationInvoice::getUrl(['record' => $r->id]))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('receipt')->label('Receipt')
                        ->icon('heroicon-o-paper-clip')->color('warning')
                        ->visible(fn (Application $r) => filled($r->receipt_file))
                        ->url(fn (Application $r) => Storage::disk('public')->url($r->receipt_file))
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('verify_receipt')->label('Verify receipt')
                        ->icon('heroicon-o-eye')->color('warning')
                        ->visible(fn (Application $r, $livewire) => ! static::isDecisionView($livewire) && filled($r->receipt_file))
                        ->modalHeading(fn (Application $r) => "Verify payment receipt — {$r->name}")
                        ->modalWidth('3xl')
                        ->modalSubmitActionLabel('Confirm payment')
                        ->modalCancelActionLabel('Close')
                        ->modalContent(fn (Application $r) => view('filament.modals.receipt-preview', [
                            'application' => $r,
                            'url'         => Storage::disk('public')->url($r->receipt_file),
                            'ext'         => strtolower(pathinfo($r->receipt_file, PATHINFO_EXTENSION)),
                        ]))
                        ->action(function (Application $r) {
                            $r->update(['payment_status' => 'paid', 'status' => 'payment_confirmed']);
                            Notification::make()->title('Payment confirmed')
                                ->body("{$r->name} moved to Confirmed.")->success()->send();
                        }),

                    Tables\Actions\Action::make('confirm_payment')->label('Confirm payment')
                        ->icon('heroicon-o-check-badge')->color('success')
                        ->visible(fn (Application $r, $livewire) => ! static::isDecisionView($livewire) && $r->payment_status !== 'paid')
                        ->requiresConfirmation()
                        ->action(function (Application $r) {
                            $r->update(['payment_status' => 'paid', 'status' => 'payment_confirmed']);
                            Notification::make()->title('Payment confirmed')
                                ->body("{$r->name} is now in the Confirmed queue.")->success()->send();
                        }),
                ])->label('Actions')->icon('heroicon-m-ellipsis-vertical')->button()->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('confirm_payment_bulk')->label('Confirm payment')
                        ->icon('heroicon-o-check-badge')->color('success')
                        ->visible(fn ($livewire) => ! static::isDecisionView($livewire))
                        ->action(function ($records) {
                            foreach ($records as $r) { $r->update(['payment_status' => 'paid', 'status' => 'payment_confirmed']); }
                            Notification::make()->title('Selected applicants moved to Confirmed')->success()->send();
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('applied_at', 'desc')
            ->striped();
    }

    /** True when the active list tab is the "Confirmed" intake review queue. */
    public static function isDecisionView($livewire): bool
    {
        return ($livewire->activeTab ?? null) === 'confirmed';
    }

    public static function transition(Application $r, string $status, string $msg, array $extra = []): void
    {
        $r->fill(array_merge(['status' => $status], $extra));
        $r->save();
        if ($status === 'accepted') {
            $r->ensureInvoiceNo();
        }
        Notification::make()->title($msg)->success()->send();
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListApplications::route('/'),
            'create' => Pages\CreateApplication::route('/create'),
            'view'   => Pages\ViewApplication::route('/{record}'),
            'edit'   => Pages\EditApplication::route('/{record}/edit'),
        ];
    }
}
