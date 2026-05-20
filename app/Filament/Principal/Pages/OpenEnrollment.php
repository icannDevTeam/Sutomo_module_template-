<?php

namespace App\Filament\Principal\Pages;

use App\Models\Application;
use App\Models\EnrollmentPeriod;
use App\Models\PaymentAccount;
use App\Support\VirtualAccount;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;

class OpenEnrollment extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationGroup = 'Enrollment';
    protected static ?int $navigationSort = -10;
    protected static ?string $title = 'Open Enrollment';
    protected static ?string $navigationLabel = 'Open Enrollment';
    protected static ?string $slug = 'open-enrollment';
    protected static string $view = 'filament.principal.pages.open-enrollment';

    /** Reached via the dashboard EnrollmentStatusCard, not the sidebar. */
    protected static bool $shouldRegisterNavigation = false;

    /** Currently-selected unit for the per-unit Enrollment Info editor.
     *  URL-bound so switching units is a full page nav (avoids Livewire
     *  state-diff edge cases when re-opening modals across units). */
    #[Url(as: 'unit')]
    public ?string $unit = 'smp';

    /** Locked yayasan-wide application fee (Rp). Used as default + hard min/max. */
    public const APPLICATION_FEE_IDR = 300000;

    public const UNIT_OPTIONS = [
        'sma'         => 'SMA',
        'smp'         => 'SMP',
        'sd'          => 'SD',
        'tk'          => 'TK',
        'playgroup'   => 'Playgroup',
        'pre_nursery' => 'Pre-Nursery',
    ];

    public function mount(): void
    {
        if (! array_key_exists((string) $this->unit, self::UNIT_OPTIONS)) {
            $this->unit = 'smp';
        }
    }

    /** Per-unit storage path so each unit keeps its own enrollment guidelines. */
    protected function infoStoragePath(?string $unit = null): string
    {
        $u = $unit ?? $this->unit ?? 'smp';
        return "public/settings/enrollment-info-{$u}.json";
    }

    protected function getViewData(): array
    {
        $periods = EnrollmentPeriod::query()->orderByDesc('opens_at')->get();
        $currentPeriod = $periods->first();
        $enrollmentInfo = $this->loadEnrollmentInfo();

        return [
            'currentPeriod'  => $currentPeriod,
            'periods'        => $periods,
            'enrollmentInfo' => $enrollmentInfo,
            'unit'           => $this->unit,
            'unitLabel'      => self::UNIT_OPTIONS[$this->unit] ?? $this->unit,
            'unitOptions'    => self::UNIT_OPTIONS,
            'unitVaDigit'    => VirtualAccount::unitDigit($this->unit),
            'vaPurposes'     => VirtualAccount::PURPOSE_DIGITS,
            'vaPurposeLabels'=> VirtualAccount::PURPOSE_LABELS,
            'applicationFee' => self::APPLICATION_FEE_IDR,
            'vaAccounts'     => PaymentAccount::query()
                ->where('unit', $this->unit)
                ->where('is_active', true)
                ->orderBy('purpose')->get(),
        ];
    }

    /* -----------------------------------------------------------------
       Actions
       ----------------------------------------------------------------- */
    protected function getHeaderActions(): array
    {
        return [
            $this->createPeriodAction(),
            $this->saveEnrollmentInfoAction(),
        ];
    }

    /**
     * Rebuild meta.observation.days for every accepted/observing application in
     * the period using the period's window. Preserves any prior attendance that
     * still maps to a date in the new window.
     */
    public static function backfillObservationDays(EnrollmentPeriod $period): int
    {
        if (! $period->observation_start_date) return 0;
        $days = max(3, (int) ($period->observation_days ?? 5));

        $dates = [];
        $cursor = \Illuminate\Support\Carbon::parse($period->observation_start_date);
        for ($i = 0; $i < $days; $i++) {
            while ($cursor->isWeekend()) $cursor->addDay();
            $dates[] = $cursor->toDateString();
            $cursor->addDay();
        }

        $touched = 0;
        $apps = Application::query()
            ->where('enrollment_period_id', $period->id)
            ->whereIn('status', ['accepted','dev_fee','books','class_assigned','observing','id_issued','tuition','activated'])
            ->get();

        foreach ($apps as $a) {
            $meta = $a->meta ?? [];
            $prior = data_get($meta, 'observation.days', []);
            $next = [];
            foreach ($dates as $d) {
                $next[$d] = $prior[$d] ?? ['present' => null, 'by' => null, 'at' => null];
            }
            $meta['observation'] = ['start_date' => $period->observation_start_date->toDateString(), 'days' => $next];
            $a->meta = $meta;
            $a->save();
            $touched++;
        }

        return $touched;
    }

    public function createPeriodAction(): Action
    {
        return Action::make('createPeriod')
            ->label('Create New Period')
            ->icon('heroicon-o-plus-circle')
            ->color('primary')
            ->form([
                Forms\Components\Section::make('Create New Enrollment Period')
                    ->description('Schedule and configure a new enrollment period for applicants.')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->placeholder('e.g., PPDB 2026/2027 Semester 1')
                                ->helperText('Give this period a descriptive name.'),
                            
                            Forms\Components\Select::make('campus')
                                ->required()
                                ->options([
                                    'Sutomo 1' => 'Sutomo 1',
                                    'Sutomo 2' => 'Sutomo 2',
                                ])
                                ->default('Sutomo 2'),
                            
                            Forms\Components\Select::make('unit')
                                ->required()
                                ->options([
                                    'Pre-Nursery' => 'Pre-Nursery',
                                    'Playgroup'   => 'Playgroup',
                                    'TK'          => 'TK',
                                    'SD'          => 'SD',
                                    'SMP'         => 'SMP',
                                    'SMA'         => 'SMA',
                                ])
                                ->default('SMP'),
                        ]),

                        Forms\Components\Grid::make(4)->schema([
                            Forms\Components\Select::make('school_level')
                                ->required()
                                ->label('School Level Target')
                                ->options([
                                    'all' => 'All Levels',
                                    'TK'  => 'TK Only',
                                    'SD'  => 'SD Only',
                                    'SMP' => 'SMP Only',
                                    'SMA' => 'SMA Only',
                                ])
                                ->default('all')
                                ->helperText('Which school level is this enrollment for?'),
                            
                            Forms\Components\DatePicker::make('opens_at')
                                ->required()
                                ->label('Opens')
                                ->helperText('When enrollment opens'),
                            
                            Forms\Components\DatePicker::make('closes_at')
                                ->required()
                                ->label('Closes')
                                ->helperText('When enrollment closes'),
                            
                            Forms\Components\DateTimePicker::make('exam_starts_at')
                                ->label('Exam Date & Time')
                                ->helperText('Placement exam schedule'),
                        ]),

                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('quota')
                                ->numeric()
                                ->default(0)
                                ->helperText('Max number of students'),
                            
                            Forms\Components\TextInput::make('pass_threshold')
                                ->numeric()
                                ->default(70)
                                ->label('Pass Score')
                                ->helperText('Minimum score to pass'),
                            
                            Forms\Components\TextInput::make('fail_threshold')
                                ->numeric()
                                ->default(60)
                                ->label('Fail Score')
                                ->helperText('Below this is automatic fail'),
                        ]),

                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('application_fee')
                                ->numeric()->required()
                                ->prefix('Rp')
                                ->default(self::APPLICATION_FEE_IDR)
                                ->minValue(self::APPLICATION_FEE_IDR)
                                ->maxValue(self::APPLICATION_FEE_IDR)
                                ->readOnly()
                                ->dehydrated()
                                ->label('Application Fee')
                                ->helperText('Locked to Rp 300.000 across all units by Yayasan policy.'),
                            Forms\Components\TextInput::make('payment_expiry_hours')
                                ->numeric()->minValue(1)->required()
                                ->suffix('hours')
                                ->default(24)
                                ->label('Payment Window')
                                ->helperText('VA expires this many hours after the applicant submits.'),
                        ]),
                    ]),
            ])
            ->action(function (array $data) {
                $data['status'] = 'draft';
                // Yayasan-wide policy: application fee is always Rp 300.000.
                $data['application_fee'] = self::APPLICATION_FEE_IDR;
                $data['payment_expiry_hours'] = $data['payment_expiry_hours'] ?? 24;
                EnrollmentPeriod::create($data);
                
                Notification::make()
                    ->title('Enrollment period created')
                    ->body('Period: ' . $data['name'])
                    ->success()
                    ->send();
            });
    }

    /** Build the form payload (info JSON + VA repeater rows) for a given unit. */
    protected function buildFormStateForUnit(string $unit): array
    {
        $info = $this->loadEnrollmentInfo($unit);
        $info['unit'] = $unit;
        $info['va_accounts'] = PaymentAccount::query()
            ->where('unit', $unit)
            ->orderBy('purpose')
            ->get()
            ->map(fn ($p) => [
                'id'           => $p->id,
                'purpose'      => $p->purpose,
                'bank_name'    => $p->bank_name,
                'account_no'   => $p->account_no,
                'account_name' => $p->account_name,
                'is_active'    => (bool) $p->is_active,
                'notes'        => $p->notes,
            ])->all();
        return $info;
    }

    public function saveEnrollmentInfoAction(): Action
    {
        return Action::make('saveEnrollmentInfo')
            ->label('Edit Enrollment Info')
            ->icon('heroicon-o-document-text')
            ->color('gray')
            ->modalWidth('5xl')
            ->modalHeading('Edit Enrollment Info')
            ->modalDescription('Pick the unit first — every other tab (Header, Requirements, Guide, Dates, Flyers, VA Accounts) is scoped to that unit.')
            ->fillForm(fn () => $this->buildFormStateForUnit($this->unit ?? 'smp'))
            ->form([
                Forms\Components\Section::make('Unit')
                    ->description('Each unit publishes its own application page — select the unit you are configuring. Switching units loads that unit’s saved values.')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Forms\Components\Select::make('unit')
                            ->label('Unit')
                            ->options(self::UNIT_OPTIONS)
                            ->required()->live()
                            ->default(fn () => $this->unit ?? 'smp')
                            ->helperText('VA digit & instructions follow the selected unit.')
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if (! $state || ! array_key_exists($state, self::UNIT_OPTIONS)) return;
                                $this->unit = $state;
                                $payload = $this->buildFormStateForUnit($state);
                                foreach ($payload as $k => $v) {
                                    if ($k === 'unit') continue;
                                    $set($k, $v);
                                }
                            }),
                    ])->columns(1),
                Forms\Components\Tabs::make()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Header')
                            ->schema([
                                Forms\Components\TextInput::make('headline')->required(),
                                Forms\Components\Textarea::make('intro')->rows(2),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('Requirements')
                            ->schema([
                                Forms\Components\Textarea::make('requirements')
                                    ->rows(4)
                                    ->label('Syarat Pendaftaran')
                                    ->helperText('One rule per line.'),
                                Forms\Components\Textarea::make('documents')
                                    ->rows(6)
                                    ->label('Dokumen Persyaratan')
                                    ->helperText('One document per line.'),
                                Forms\Components\Textarea::make('documents_note')
                                    ->rows(2)
                                    ->label('NB / catatan'),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('Guide')
                            ->schema([
                                Forms\Components\Textarea::make('guide')
                                    ->rows(10)
                                    ->label('Panduan Pendaftaran Online')
                                    ->helperText('Numbered steps, one per line.'),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('Dates & Contacts')
                            ->schema([
                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\DatePicker::make('exam_date'),
                                    Forms\Components\DateTimePicker::make('result_date'),
                                    Forms\Components\TextInput::make('application_url')->columnSpanFull(),
                                ]),
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('contact_email')->email(),
                                    Forms\Components\TextInput::make('contact_wa')->label('WhatsApp'),
                                    Forms\Components\TextInput::make('contact_phone'),
                                    Forms\Components\TextInput::make('contact_ig')->label('Instagram'),
                                    Forms\Components\Textarea::make('address')->rows(2)->columnSpanFull(),
                                ]),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('Flyers')
                            ->schema([
                                Forms\Components\FileUpload::make('flyers')
                                    ->multiple()->reorderable()->maxFiles(8)->maxSize(8192)
                                    ->disk('public')->directory('enrollment/flyers')
                                    ->acceptedFileTypes(['image/*','application/pdf']),
                            ]),

                        Forms\Components\Tabs\Tab::make('VA Accounts')
                            ->icon('heroicon-o-credit-card')
                            ->schema([
                                Forms\Components\Placeholder::make('va_scheme_help')
                                    ->label('VA number format')
                                    ->content(function (Forms\Get $get) {
                                        $u = $get('unit') ?: ($this->unit ?? 'smp');
                                        return new \Illuminate\Support\HtmlString(
                                            '<div class="text-xs leading-relaxed text-gray-600">'
                                            .'<code class="px-1.5 py-0.5 rounded bg-gray-100">14339</code> + '
                                            .'<code class="px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700">'
                                            . e(VirtualAccount::unitDigit($u) ?? 'X')
                                            .'</code> + '
                                            .'<code class="px-1.5 py-0.5 rounded bg-rose-50 text-rose-700">Y</code> + '
                                            .'<code class="px-1.5 py-0.5 rounded bg-gray-100">&lt;ref&gt;</code>'
                                            .'<br>Unit digit <strong>'.e(VirtualAccount::unitDigit($u) ?? '—').'</strong> for '
                                            . e(self::UNIT_OPTIONS[$u] ?? '')
                                            .' · Purpose: 0 Tuition · 1 Admin · 2 Textbooks · 3 Dev Fee · 4 Enrollment'
                                            .'<br><span class="text-amber-700">Note: VA account number is provided by the Unit head.</span>'
                                            .'</div>'
                                        );
                                    }),
                                Forms\Components\Repeater::make('va_accounts')
                                    ->label(fn (Forms\Get $get) => 'VA accounts for ' . (self::UNIT_OPTIONS[$get('unit') ?? 'smp'] ?? ''))
                                    ->addActionLabel('Add VA account')
                                    ->reorderable(false)
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string =>
                                        ($state['bank_name'] ?? null)
                                            ? trim(($state['bank_name']) . ' · ' . ($state['account_no'] ?? ''))
                                            : null
                                    )
                                    ->schema([
                                        Forms\Components\Hidden::make('id'),
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\Select::make('purpose')
                                                ->options(PaymentAccount::PURPOSES)
                                                ->required()->default('enrollment')->live()
                                                ->helperText(function (Forms\Get $get) {
                                                    $u = $get('../../unit') ?: ($this->unit ?? 'smp');
                                                    return 'VA prefix: 14339' . (VirtualAccount::unitDigit($u) ?? '—')
                                                        . (VirtualAccount::purposeDigit($get('purpose')) ?? '—') . ' + <ref>';
                                                }),
                                            Forms\Components\TextInput::make('bank_name')
                                                ->required()->default('BCA')->placeholder('BCA'),
                                        ]),
                                        Forms\Components\TextInput::make('account_no')
                                            ->label('Account number / VA')->required()
                                            ->placeholder('14339142610011')
                                            ->helperText('Provided by the Unit head.'),
                                        Forms\Components\TextInput::make('account_name')
                                            ->required()->placeholder('Yayasan Sutomo — Pendaftaran'),
                                        Forms\Components\Toggle::make('is_active')->default(true)->inline(false),
                                        Forms\Components\Textarea::make('notes')->rows(2),
                                    ])
                                    ->default([]),
                            ]),
                    ]),
            ])
            ->action(function (array $data) {
                $unit = $data['unit'] ?? $this->unit ?? 'smp';
                if (! array_key_exists($unit, self::UNIT_OPTIONS)) $unit = 'smp';
                unset($data['unit']);
                $this->unit = $unit;

                // 1. Sync VA accounts for this unit
                $rows = $data['va_accounts'] ?? [];
                unset($data['va_accounts']);
                $keptIds = [];
                $campusFallback = optional(PaymentAccount::query()->where('unit', $unit)->first())->campus
                    ?? 'CMP-001';
                foreach ($rows as $row) {
                    if (empty($row['account_no']) || empty($row['bank_name'])) continue;
                    $payload = [
                        'unit'         => $unit,
                        'purpose'      => $row['purpose'] ?? 'enrollment',
                        'bank_name'    => $row['bank_name'],
                        'account_no'   => $row['account_no'],
                        'account_name' => $row['account_name'] ?? '',
                        'is_active'    => (bool) ($row['is_active'] ?? true),
                        'notes'        => $row['notes'] ?? null,
                    ];
                    if (! empty($row['id'])) {
                        $acc = PaymentAccount::find($row['id']);
                        if ($acc) { $acc->update($payload); $keptIds[] = $acc->id; continue; }
                    }
                    $payload['campus'] = $campusFallback;
                    $new = PaymentAccount::create($payload);
                    $keptIds[] = $new->id;
                }
                // Delete rows for this unit that were removed in the modal
                PaymentAccount::query()->where('unit', $unit)
                    ->when($keptIds, fn ($q) => $q->whereNotIn('id', $keptIds))
                    ->delete();

                // 2. Persist enrollment info JSON for this unit
                Storage::put($this->infoStoragePath($unit), json_encode($data, JSON_PRETTY_PRINT));
                
                Notification::make()
                    ->title('Enrollment information saved for ' . (self::UNIT_OPTIONS[$unit] ?? strtoupper($unit)))
                    ->success()
                    ->send();
            });
    }

    /* -----------------------------------------------------------------
       Period Management Actions (for the list)
       ----------------------------------------------------------------- */
    public function openPeriodAction(): Action
    {
        return Action::make('openPeriod')
            ->label('Open')
            ->icon('heroicon-o-megaphone')
            ->color('success')
            ->requiresConfirmation()
            ->action(function (array $arguments) {
                $period = EnrollmentPeriod::find($arguments['period']);
                $period?->update(['status' => 'open']);
                Notification::make()->title('Period opened')->success()->send();
            });
    }

    public function closePeriodAction(): Action
    {
        return Action::make('closePeriod')
            ->label('Close')
            ->icon('heroicon-o-lock-closed')
            ->color('danger')
            ->requiresConfirmation()
            ->action(function (array $arguments) {
                $period = EnrollmentPeriod::find($arguments['period']);
                $period?->update(['status' => 'closed']);
                Notification::make()->title('Period closed')->warning()->send();
            });
    }

    public function editPeriodAction(): Action
    {
        return Action::make('editPeriod')
            ->label('Edit')
            ->icon('heroicon-o-pencil')
            ->color('gray')
            ->fillForm(fn (array $arguments) => EnrollmentPeriod::find($arguments['period'])?->toArray() ?? [])
            ->form([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\Select::make('campus')->required()->options(['Sutomo 1' => 'Sutomo 1', 'Sutomo 2' => 'Sutomo 2']),
                    Forms\Components\Select::make('unit')->required()->options([
                        'Pre-Nursery' => 'Pre-Nursery', 'Playgroup' => 'Playgroup', 'TK' => 'TK',
                        'SD' => 'SD', 'SMP' => 'SMP', 'SMA' => 'SMA',
                    ]),
                    Forms\Components\Select::make('school_level')->required()->options([
                        'all' => 'All Levels', 'TK' => 'TK Only', 'SD' => 'SD Only', 'SMP' => 'SMP Only', 'SMA' => 'SMA Only',
                    ]),
                ]),
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\DatePicker::make('opens_at')->required(),
                    Forms\Components\DatePicker::make('closes_at')->required(),
                ]),
                Forms\Components\DateTimePicker::make('exam_starts_at'),
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\TextInput::make('quota')->numeric(),
                    Forms\Components\TextInput::make('pass_threshold')->numeric(),
                    Forms\Components\TextInput::make('fail_threshold')->numeric(),
                ]),
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('application_fee')
                        ->numeric()->prefix('Rp')->label('Application Fee')
                        ->default(self::APPLICATION_FEE_IDR)
                        ->minValue(self::APPLICATION_FEE_IDR)
                        ->maxValue(self::APPLICATION_FEE_IDR)
                        ->readOnly()->dehydrated()
                        ->helperText('Locked to Rp 300.000 across all units by Yayasan policy.'),
                    Forms\Components\TextInput::make('payment_expiry_hours')->numeric()->minValue(1)->suffix('hours')->label('Payment Window'),
                ]),
            ])
            ->action(function (array $arguments, array $data) {
                $period = EnrollmentPeriod::find($arguments['period']);
                $data['application_fee'] = self::APPLICATION_FEE_IDR; // policy lock
                $period?->update($data);
                Notification::make()->title('Period updated')->success()->send();
            });
    }

    public function deletePeriodAction(): Action
    {
        return Action::make('deletePeriod')
            ->label('Delete')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->action(function (array $arguments) {
                $period = EnrollmentPeriod::find($arguments['period']);
                $appCount = $period?->applications()->count() ?? 0;
                
                if ($appCount > 0) {
                    Notification::make()
                        ->title('Cannot delete')
                        ->body("This period has {$appCount} applications.")
                        ->danger()
                        ->send();
                    return;
                }
                
                $period?->delete();
                Notification::make()->title('Period deleted')->success()->send();
            });
    }

    /* -----------------------------------------------------------------
       Helpers
       ----------------------------------------------------------------- */
    protected function loadEnrollmentInfo(?string $unit = null): array
    {
        $path = $this->infoStoragePath($unit);
        if (Storage::exists($path)) {
            $raw = json_decode(Storage::get($path), true);
            if (is_array($raw)) return $raw;
        }
        return $this->defaultEnrollmentInfo($unit);
    }

    protected function defaultEnrollmentInfo(?string $unit = null): array
    {
        $u = $unit ?? $this->unit ?? 'smp';
        $label = self::UNIT_OPTIONS[$u] ?? strtoupper($u);
        return [
            'headline'      => "Penerimaan Peserta Didik Baru — {$label} Sutomo",
            'intro'         => "Selamat datang di portal pendaftaran {$label} Sutomo.",
            'requirements'  => "• Peraturan dan tata tertib sekolah.\n• Tidak merokok, minum minuman keras, dan terlibat dalam narkoba.",
            'documents'     => "1. Surat Keterangan Lulus SD\n2. Fotokopi Rapor SD\n3. Fotokopi Akta Kelahiran",
            'documents_note'=> 'Semua dokumen dimasukkan ke dalam map warna biru.',
            'guide'         => "1. Masuk ke halaman Pendaftaran SMP.\n2. Klik REGISTRASI untuk mendaftar akun.",
            'exam_date'     => '2026-06-12',
            'result_date'   => '2026-06-18 13:00',
            'application_url' => 'https://pmbsmp2.sutomo-mdn.sch.id/',
            'contact_email' => 'smpsutomo2@sutomo-mdn.sch.id',
            'contact_wa'    => '0813 6166 8828',
            'contact_phone' => '(061) 6615674',
            'contact_ig'    => 'sutomo2.medan',
            'address'       => 'Jl. Deli Indah IV No. 6, Pulo Brayan, Medan',
            'flyers'        => [],
        ];
    }
}
