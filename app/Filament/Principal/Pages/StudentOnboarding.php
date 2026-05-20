<?php

namespace App\Filament\Principal\Pages;

use App\Models\Application;
use App\Models\BookPackage;
use App\Models\EbookPack;
use App\Models\EbookPlatform;
use App\Models\EnrollmentPeriod;
use App\Models\PaymentAccount;
use App\Support\FeeSchedule;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class StudentOnboarding extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Enrollment';
    protected static ?int $navigationSort = 3;
    protected static ?string $title = 'Student Onboarding';
    protected static ?string $navigationLabel = 'Student Onboarding';
    protected static ?string $slug = 'onboarding-flow';
    protected static string $view = 'filament.principal.pages.student-onboarding';

    public string $activeTab = 'accepted';
    public ?string $campusFilter = null;
    public ?string $unitFilter = null;
    public ?string $gradeFilter = null;
    public ?string $streamFilter = null;
    public ?string $search = null;

    /* -----------------------------------------------------------------
       Header actions — Observation Configuration lives here so the
       Principal can adjust the cohort window from the page where
       attendance is actually managed.
       ----------------------------------------------------------------- */
    protected function getHeaderActions(): array
    {
        return [
            $this->setObservationWindowAction(),
        ];
    }

    public function setObservationWindowAction(): Action
    {
        return Action::make('setObservationWindow')
            ->label('Observation Configuration')
            ->icon('heroicon-o-calendar-days')
            ->color('warning')
            ->modalHeading('Cohort Observation Window')
            ->modalDescription('Pick the 5-day observation window for an enrollment period. All accepted students in this period share the same dates — teachers will mark daily attendance against these days.')
            ->modalWidth('xl')
            ->fillForm(function () {
                $current = EnrollmentPeriod::query()->orderByDesc('opens_at')->first();
                return [
                    'period_id'              => $current?->id,
                    'observation_start_date' => $current?->observation_start_date?->toDateString(),
                    'observation_days'       => $current?->observation_days ?? 5,
                ];
            })
            ->form([
                Forms\Components\Select::make('period_id')
                    ->label('Enrollment Period')
                    ->options(EnrollmentPeriod::query()->orderByDesc('opens_at')->pluck('name', 'id'))
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        $p = EnrollmentPeriod::find($state);
                        $set('observation_start_date', $p?->observation_start_date?->toDateString());
                        $set('observation_days', $p?->observation_days ?? 5);
                    }),
                Forms\Components\DatePicker::make('observation_start_date')
                    ->label('Observation Start Date (Day 1)')
                    ->required()
                    ->helperText('Weekends are skipped automatically when computing Day 2–5.'),
                Forms\Components\TextInput::make('observation_days')
                    ->label('Number of Days')
                    ->numeric()->minValue(3)->maxValue(10)->default(5)
                    ->helperText('Default is 5. Increase if the cohort needs longer observation.'),
                Forms\Components\Toggle::make('backfill')
                    ->label('Apply to existing students in this period')
                    ->helperText('Resets day-slots for every accepted/observing student in this period to the new window. Previously marked attendance is preserved when the date still exists in the new window.')
                    ->default(true),
            ])
            ->action(function (array $data) {
                $period = EnrollmentPeriod::find($data['period_id']);
                if (! $period) return;
                $period->observation_start_date = $data['observation_start_date'];
                $period->observation_days       = (int) ($data['observation_days'] ?? 5);
                $period->save();

                $applied = 0;
                if (! empty($data['backfill'])) {
                    $applied = OpenEnrollment::backfillObservationDays($period);
                }

                Notification::make()
                    ->title('Observation window saved')
                    ->body($applied
                        ? "Window applied to {$applied} student(s) in {$period->name}."
                        : "Window saved for {$period->name}. New students will inherit it.")
                    ->success()->send();
            });
    }

    /** Sub-stage codes (within the Accepted track) — used by Track B (SD Plus / SMP / SMA). */
    public const STAGES = [
        'devfee'     => 'Development Fee',
        'books'      => 'Book Purchase',
        'class'      => 'Class Assigned',
        'attendance' => 'Observation (5 days)',
        'student_id' => 'Student ID Issued',
        'eca'        => 'CCA / e-Books',
        'tuition'    => 'Tuition',
    ];

    /**
     * Sub-stage codes for Track A (TK Plus, TK Regular, SD Regular, Playgroup, Pre-Nursery).
     * Per Yayasan spec (May 2026): interview-first, then ONE combined Registration + Donation
     * payment. No class assignment / observation / student-ID / tuition tracking in this module
     * for Track A — once the combined payment is recorded the student is fully onboarded.
     */
    public const STAGES_TRACK_A = [
        'interview'  => 'Interview & Academic Test',
        'donation'   => 'Registration & Donation',
    ];

    /**
     * Track A = interview-first programs (TK Plus / TK Regular / SD Regular).
     * Track B = registration-first programs (SD Plus, SMP, SMA).
     */
    public static function trackFor(Application $a): string
    {
        $unit   = strtolower((string) $a->campus);
        $stream = strtolower((string) $a->stream);
        if (in_array($unit, ['tk', 'playgroup', 'pre_nursery'], true)) return 'A';
        if ($unit === 'sd' && $stream !== 'plus') return 'A';
        return 'B';
    }

    /** @return array<string,string> ordered stage map for this applicant. */
    public static function stagesFor(Application $a): array
    {
        return self::trackFor($a) === 'A' ? self::STAGES_TRACK_A : self::STAGES;
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetTable();
    }

    public function resetTable(): void
    {
        $this->campusFilter = null;
        $this->unitFilter = null;
        $this->gradeFilter = null;
        $this->streamFilter = null;
    }

    /* -------------------------------------------------------------
       VIEW DATA
       ------------------------------------------------------------- */
    protected function getViewData(): array
    {
        $base = Application::query()->with('enrollmentPeriod');

        $counts = [
            'accepted'   => (clone $base)->whereIn('status', self::acceptedStatuses())->count(),
            'waitlisted' => (clone $base)->where('status', 'waitlisted')->count(),
            'withdrawn'  => (clone $base)->whereIn('status', Application::TERMINAL_STATUSES)->count(),
            'failed'     => (clone $base)->where('status', 'failed')->count(),
        ];

        $q = match ($this->activeTab) {
            'waitlisted' => (clone $base)->where('status', 'waitlisted'),
            'withdrawn'  => (clone $base)->whereIn('status', Application::TERMINAL_STATUSES),
            'failed'     => (clone $base)->where('status', 'failed'),
            default      => (clone $base)->whereIn('status', self::acceptedStatuses()),
        };

        if ($this->campusFilter) $q->where('campus', $this->campusFilter);
        if ($this->unitFilter)   $q->where('unit', $this->unitFilter);
        if ($this->gradeFilter)  $q->where('grade', $this->gradeFilter);
        if ($this->streamFilter) $q->where('stream', $this->streamFilter);
        if ($this->search) {
            $s = $this->search;
            $q->where(fn ($qq) => $qq->where('name', 'like', "%{$s}%")
                ->orWhere('code', 'like', "%{$s}%")
                ->orWhere('parent_name', 'like', "%{$s}%"));
        }

        $rows = $q->orderBy('name')->get()->map(fn (Application $a) => [
            'app'    => $a,
            'stages' => self::computeStages($a),
        ]);

        return [
            'counts'   => $counts,
            'rows'     => $rows,
            'tab'      => $this->activeTab,
            'campuses' => Application::query()->whereNotNull('campus')->distinct()->orderBy('campus')->pluck('campus'),
            'units'    => Application::query()->whereNotNull('unit')->distinct()->orderBy('unit')->pluck('unit'),
            'grades'   => Application::query()->whereNotNull('grade')->distinct()->orderBy('grade')->pluck('grade'),
            'streams'  => Application::query()->whereNotNull('stream')->distinct()->orderBy('stream')->pluck('stream'),
        ];
    }

    /**
     * Statuses shown on the onboarding "accepted" tab. Includes 'passed' (eligible but
     * not yet officially accepted) plus everything in Application::ONBOARDING_STATUSES.
     */
    public static function acceptedStatuses(): array
    {
        return array_merge(['passed'], Application::ONBOARDING_STATUSES);
    }

    /**
     * Map each onboarding sub-stage to its lock/done/current state for one student.
     *
     * @return array<string, array{key:string,label:string,state:'done'|'current'|'locked',hint:?string}>
     */
    public static function computeStages(Application $a): array
    {
        $m = $a->meta ?? [];
        $checks = [
            'interview'  => filled(data_get($m, 'interview.passed_at')),
            'donation'   => filled(data_get($m, 'donation.paid_at')) || filled(data_get($m, 'devfee.paid_at')),
            'devfee'     => filled(data_get($m, 'devfee.paid_at')),
            'books'      => filled(data_get($m, 'books.bought_at')),
            'class'      => filled(data_get($m, 'class.assigned_at')),
            'attendance' => self::observationComplete($m),
            'student_id' => filled($a->assigned_student_no),
            'eca'        => filled(data_get($m, 'eca.submitted_at')) && filled(data_get($m, 'ebooks.assigned_at')),
            'tuition'    => filled(data_get($m, 'tuition.paid_at')),
        ];

        $out = [];
        $currentSet = false;
        foreach (self::stagesFor($a) as $key => $label) {
            if ($checks[$key]) {
                $out[$key] = ['key' => $key, 'label' => $label, 'state' => 'done', 'hint' => null];
            } elseif (! $currentSet) {
                $out[$key] = ['key' => $key, 'label' => $label, 'state' => 'current', 'hint' => self::stageHint($key)];
                $currentSet = true;
            } else {
                $out[$key] = ['key' => $key, 'label' => $label, 'state' => 'locked', 'hint' => self::stageHint($key, true)];
            }
        }
        return $out;
    }

    /**
     * Observation is complete once the student has 5 present-day marks recorded.
     * Reads the new `observation.days` shape ({date => {present:bool, by, at}})
     * and falls back to the legacy flat `attendance` map for older records.
     */
    protected static function observationComplete(array $meta): bool
    {
        $days = data_get($meta, 'observation.days', []);
        if (is_array($days) && $days) {
            $present = collect($days)->filter(fn ($d) => (bool) data_get($d, 'present'))->count();
            return $present >= 5;
        }
        $att = data_get($meta, 'attendance', []);
        if (! is_array($att)) return false;
        return collect($att)->filter()->count() >= 5;
    }

    protected static function stageHint(string $key, bool $locked = false): ?string
    {
        if ($locked) return 'Complete the previous step first.';
        return match ($key) {
            'interview'  => 'Record the interview & academic test result. Passing unlocks the Registration + Donation payment.',
            'donation'   => 'Confirm the combined registration & donation payment to unlock class assignment.',
            'devfee'     => 'Confirm the development fee payment to unlock book purchase.',
            'books'      => 'Record book purchase (admin enters the payment account).',
            'class'      => 'Assign a class based on unit, stream and grade.',
            'attendance' => 'Mark observation attendance — 5 days required. After 3 absences a check-up will be raised.',
            'student_id' => 'Issue the official school ID (also the VA for fee payment).',
            'eca'        => 'Submit CCA / ECA preferences and assign e-books.',
            'tuition'    => 'Confirm first tuition payment to finalise activation.',
            default      => null,
        };
    }

    /* -------------------------------------------------------------
       STAGE ACTIONS
       ------------------------------------------------------------- */

    public function confirmDevFeeAction(): Action
    {
        return Action::make('confirmDevFee')
            ->label('Confirm Development Fee')
            ->icon('heroicon-m-banknotes')->color('success')->size('sm')
            ->form(function (array $arguments) {
                $a = Application::find($arguments['id']);
                $summary = $a ? FeeSchedule::summary($a) : [];
                $devAmt = $summary['development']['amount'] ?? 0;
                return [
                    Forms\Components\Placeholder::make('breakdown')
                        ->label('Fee summary (' . FeeSchedule::TRACKS[FeeSchedule::resolveTrack($a)] . ')')
                        ->content(collect($summary)->map(fn ($r) => $r['label'] . ': ' . FeeSchedule::rp($r['amount']))->implode(' · ')),
                    Forms\Components\Select::make('track')->label('Track / Programme')
                        ->options(FeeSchedule::TRACKS)
                        ->default(FeeSchedule::resolveTrack($a))->required(),
                    Forms\Components\TextInput::make('amount')->label('Amount paid (Rp)')
                        ->numeric()->default($devAmt)->required(),
                    Forms\Components\TextInput::make('reference')->label('Reference / VA No.')->maxLength(40),
                ];
            })
            ->action(function (array $arguments, array $data) {
                $a = Application::find($arguments['id']);
                if (! $a) return;
                // Ensure an invoice exists for anyone entering onboarding, regardless of
                // whether they reached this step from ApplicationResource accept (which
                // also sets invoice_no) or directly from 'passed'.
                $a->ensureInvoiceNo();
                $meta = $a->meta ?? [];
                $meta['track'] = $data['track'];
                $meta['devfee'] = [
                    'amount'    => (int) $data['amount'],
                    'reference' => $data['reference'] ?? null,
                    'paid_at'   => now()->toIso8601String(),
                    'by'        => auth()->user()?->name ?? 'Principal',
                ];
                $a->meta = $meta;
                $a->status = 'dev_fee';
                $a->save();
                Notification::make()->title('Development fee recorded')->body("Books step unlocked for {$a->name}.")->success()->send();
            });
    }

    /**
     * Track A first step: Interview & Academic Test.
     * Stores the score + recommendation onto the application; passing unlocks
     * the combined Registration + Donation payment step.
     */
    public function recordInterviewAction(): Action
    {
        return Action::make('recordInterview')
            ->label('Record Interview & Test')
            ->icon('heroicon-m-academic-cap')->color('info')->size('sm')
            ->modalDescription('Capture the interview & academic test outcome. Track A applicants only proceed to payment after passing.')
            ->fillForm(function (array $arguments) {
                $a = Application::find($arguments['id']);
                return [
                    'score'   => $a?->placement_score,
                    'outcome' => 'pass',
                ];
            })
            ->form([
                Forms\Components\TextInput::make('score')->label('Academic test score')
                    ->numeric()->minValue(0)->maxValue(100)->required(),
                Forms\Components\Select::make('outcome')->label('Outcome')->required()
                    ->options(['pass' => 'Pass', 'fail' => 'Fail'])
                    ->default('pass'),
                Forms\Components\Textarea::make('notes')->label('Interview notes')->rows(3),
            ])
            ->action(function (array $arguments, array $data) {
                $a = Application::find($arguments['id']);
                if (! $a) return;
                $meta = $a->meta ?? [];
                $passed = ($data['outcome'] ?? 'pass') === 'pass';
                $meta['interview'] = [
                    'score'    => (int) $data['score'],
                    'outcome'  => $data['outcome'],
                    'notes'    => $data['notes'] ?? null,
                    'passed_at'=> $passed ? now()->toIso8601String() : null,
                    'by'       => auth()->user()?->name ?? 'Principal',
                ];
                $a->meta = $meta;
                $a->placement_score = (int) $data['score'];
                if (! $passed) {
                    $a->status = 'failed';
                    $a->save();
                    Notification::make()->title('Marked as failed')->body("{$a->name} did not pass the interview.")->warning()->send();
                    return;
                }
                $a->save();
                Notification::make()->title('Interview & test recorded')
                    ->body("Registration + Donation step unlocked for {$a->name}.")->success()->send();
            });
    }

    /**
     * Track A combined payment: Registration Fee + Donation Fee in one step
     * (TK Plus, TK Regular, SD Regular). Skips the Book / Dev-fee stages.
     */
    public function confirmDonationAction(): Action    {
        return Action::make('confirmDonation')
            ->label('Confirm Registration & Donation')
            ->icon('heroicon-m-banknotes')->color('success')->size('sm')
            ->modalDescription('Record the combined registration + donation payment for this Track A applicant. Unlocks class assignment.')
            ->form(function (array $arguments) {
                $a = Application::find($arguments['id']);
                $summary = $a ? FeeSchedule::summary($a) : [];
                $reg = $summary['registration']['amount'] ?? 300000;
                $dev = $summary['development']['amount'] ?? 0;
                $total = $reg + $dev;
                return [
                    Forms\Components\Placeholder::make('breakdown')
                        ->label('Combined amount')
                        ->content('Registration ' . FeeSchedule::rp($reg) . ' + Donation ' . FeeSchedule::rp($dev) . ' = ' . FeeSchedule::rp($total)),
                    Forms\Components\TextInput::make('amount')->label('Total received (Rp)')
                        ->numeric()->default($total)->required(),
                    Forms\Components\TextInput::make('reference')->label('Reference / VA No.')->maxLength(40),
                ];
            })
            ->action(function (array $arguments, array $data) {
                $a = Application::find($arguments['id']);
                if (! $a) return;
                $a->ensureInvoiceNo();
                $meta = $a->meta ?? [];
                $now  = now()->toIso8601String();
                $by   = auth()->user()?->name ?? 'Principal';
                $payload = [
                    'amount'    => (int) $data['amount'],
                    'reference' => $data['reference'] ?? null,
                    'paid_at'   => $now,
                    'by'        => $by,
                ];
                $meta['donation'] = $payload;
                // Mirror onto `devfee` for legacy reports/pipeline columns.
                $meta['devfee'] = $payload;
                $a->meta = $meta;
                // Track A has no further stages — combined payment fully activates the student.
                $a->status = 'activated';
                $a->save();
                Notification::make()->title('Registration & donation recorded')
                    ->body("{$a->name} is now fully onboarded.")->success()->send();
            });
    }

    public function recordBooksAction(): Action
    {
        return Action::make('recordBooks')
            ->label('Record Book Purchase')
            ->icon('heroicon-m-book-open')->color('warning')->size('sm')
            ->modalWidth('3xl')
            ->modalDescription('Pick the catalog package for the student\'s cohort and the campus payment account that received the funds. Line-items are snapshotted onto the application so future catalog edits will not rewrite history.')
            ->form(function (array $arguments) {
                $a = Application::find($arguments['id']);
                // NOTE: In Application, `campus` column actually stores the unit slug (e.g. 'sd')
                // and `unit` column stores the campus code (e.g. 'CMP-001').
                $unitSlug   = $a->campus;
                $campusCode = $a->unit;

                $packages = BookPackage::query()
                    ->where('is_active', true)
                    ->where('unit', $unitSlug)
                    ->where(function ($q) use ($a) {
                        $q->whereNull('grade')->orWhere('grade', $a->grade);
                    })
                    ->orderByRaw('grade IS NULL')
                    ->orderBy('name')
                    ->get();

                $accounts = PaymentAccount::query()
                    ->where('is_active', true)
                    ->where('purpose', 'books')
                    ->where('campus', $campusCode)
                    ->orderBy('bank_name')
                    ->get();

                return [
                    Forms\Components\Placeholder::make('cohort')
                        ->label('Cohort')
                        ->content(\App\Support\SchoolDirectory::unitLabel($unitSlug) . ' · Grade ' . ($a->grade ?? '—')
                            . ' · ' . (\App\Support\SchoolDirectory::campusLabel($campusCode) ?? $campusCode)),

                    Forms\Components\Select::make('book_package_id')
                        ->label('Book package')
                        ->options($packages->pluck('name', 'id'))
                        ->required()
                        ->live()
                        ->helperText($packages->isEmpty()
                            ? 'No active package for this cohort. Add one in Enrollment, Book Catalog.'
                            : null)
                        ->disabled($packages->isEmpty()),

                    Forms\Components\Placeholder::make('package_preview')
                        ->label('Package contents')
                        ->visible(fn (Forms\Get $get) => (bool) $get('book_package_id'))
                        ->content(function (Forms\Get $get) {
                            $pkg = BookPackage::find($get('book_package_id'));
                            if (! $pkg) return '—';
                            $rows = collect($pkg->items ?? [])->map(function ($it) {
                                $sub = (int) ($it['qty'] ?? 0) * (int) ($it['unit_price'] ?? 0);
                                return '• ' . ($it['title'] ?? '—')
                                    . ' × ' . (int) ($it['qty'] ?? 0)
                                    . ' = Rp ' . number_format($sub, 0, ',', '.');
                            })->implode("\n");
                            $total = 'Total: Rp ' . number_format((int) $pkg->total, 0, ',', '.');
                            return new \Illuminate\Support\HtmlString(
                                '<pre class="text-xs whitespace-pre-wrap font-mono">' . e($rows) . "\n\n" . e($total) . '</pre>'
                            );
                        }),

                    Forms\Components\Select::make('payment_account_id')
                        ->label('Payment received in')
                        ->options($accounts->mapWithKeys(fn ($acc) => [
                            $acc->id => "{$acc->bank_name} · {$acc->account_no} · {$acc->account_name}",
                        ]))
                        ->required()
                        ->helperText($accounts->isEmpty()
                            ? 'No active Books account for this campus. Add one in Enrollment, Payment Accounts.'
                            : null)
                        ->disabled($accounts->isEmpty()),

                    Forms\Components\Textarea::make('note')->label('Note (optional)')->rows(2),
                ];
            })
            ->action(function (array $arguments, array $data) {
                $a = Application::find($arguments['id']);
                if (! $a) return;
                $pkg = BookPackage::find($data['book_package_id']);
                $acc = PaymentAccount::find($data['payment_account_id']);
                if (! $pkg || ! $acc) {
                    Notification::make()->title('Missing package or account')->danger()->send();
                    return;
                }
                $meta = $a->meta ?? [];
                $meta['books'] = [
                    'package_id'   => $pkg->id,
                    'package_name' => $pkg->name,
                    'items'        => $pkg->items,   // snapshot
                    'total'        => (int) $pkg->total,
                    'account_id'   => $acc->id,
                    'account_name' => $acc->account_name,
                    'account_no'   => $acc->account_no,
                    'bank_name'    => $acc->bank_name,
                    'note'         => $data['note'] ?? null,
                    'bought_at'    => now()->toIso8601String(),
                ];
                $a->meta = $meta;
                $a->status = 'books';
                $a->save();
                Notification::make()->title('Books recorded')
                    ->body("{$pkg->name} · Rp " . number_format((int) $pkg->total, 0, ',', '.') . ' via ' . $acc->bank_name)
                    ->success()->send();
            });
    }

    public function assignClassAction(): Action
    {
        return Action::make('assignClass')
            ->label('Assign Class')
            ->icon('heroicon-m-rectangle-group')->color('info')->size('sm')
            ->form(function (array $arguments) {
                $a = Application::find($arguments['id']);
                $hint = "Eligible: {$a->grade} · " . strtoupper($a->campus ?? '—') . ($a->stream ? ' · ' . strtoupper($a->stream) : '');
                return [
                    Forms\Components\Placeholder::make('h')->label('Placement criteria')->content($hint),
                    Forms\Components\Select::make('class_label')->label('Class')
                        ->options(self::candidateClasses($a))
                        ->searchable()->required(),
                    Forms\Components\TextInput::make('homeroom_teacher')->label('Homeroom teacher')->placeholder('Optional'),
                ];
            })
            ->action(function (array $arguments, array $data) {
                $a = Application::find($arguments['id']);
                if (! $a) return;
                $meta = $a->meta ?? [];
                $meta['class'] = [
                    'label'    => $data['class_label'],
                    'teacher'  => $data['homeroom_teacher'] ?? null,
                    'assigned_at' => now()->toIso8601String(),
                ];
                $a->meta = $meta;
                $a->status = 'class_assigned';
                $a->save();
                Notification::make()->title('Class assigned')->body("{$a->name} → {$data['class_label']}")->success()->send();
            });
    }

    /** Generate plausible class labels e.g. "SMA-11-IPA-A". */
    protected static function candidateClasses(Application $a): array
    {
        $campus = strtoupper($a->campus ?? 'X');
        $grade  = $a->grade ?? '?';
        $stream = $a->stream ? strtoupper($a->stream) : null;
        $out = [];
        foreach (['A','B','C','D'] as $sec) {
            $label = $campus . '-' . $grade . ($stream ? "-{$stream}" : '') . "-{$sec}";
            $out[$label] = $label;
        }
        return $out;
    }

    public function markAttendanceAction(): Action
    {
        return Action::make('markAttendance')
            ->label('Observation Attendance')
            ->modalHeading('Observation — 5-day Attendance')
            ->modalDescription('Teachers record daily attendance. Principal can override. Stage advances automatically once 5 present days are reached; 3+ absences raise a check-up flag.')
            ->modalWidth('5xl')
            ->icon('heroicon-m-clipboard-document-check')->color('primary')->size('sm')
            ->fillForm(function (array $arguments): array {
                $a = Application::find($arguments['id']);
                if (! $a) return [];
                $obs = self::observationState($a);
                $form = ['start_date' => $obs['start_date']];
                foreach ($obs['rows'] as $i => $row) {
                    $form["day_{$i}_status"] = $row['status'];      // pending|present|absent
                    $form["day_{$i}_by"]     = $row['by'] ?? '';
                }
                return $form;
            })
            ->form(function (array $arguments) {
                $a = Application::find($arguments['id']);
                $obs = $a ? self::observationState($a) : ['started' => false, 'rows' => [], 'start_date' => null, 'present' => 0, 'absent' => 0, 'pending' => 5, 'end_date' => null];

                // ─── Not started yet ───
                if (! $obs['started']) {
                    $periodName = $a?->enrollmentPeriod?->name ?? 'this period';
                    return [
                        Forms\Components\Placeholder::make('no_window')
                            ->label('')
                            ->content(new \Illuminate\Support\HtmlString(
                                '<div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">'
                                . '<div class="flex items-center gap-2 font-semibold"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z"/></svg> Observation window not set for ' . e($periodName) . '</div>'
                                . '<p class="mt-2">The cohort observation window is configured on this page.</p>'
                                . '<p class="mt-2">Click <strong>Observation Configuration</strong> at the top right, pick Day 1, and leave <em>"Apply to existing students"</em> ticked so this student gets the 5-day grid. It will appear here once saved.</p>'
                                . '</div>'
                            )),
                    ];
                }

                // ─── Started: show grid ───
                $startedFmt = \Illuminate\Support\Carbon::parse($obs['start_date'])->translatedFormat('d M Y');
                $endFmt     = \Illuminate\Support\Carbon::parse($obs['end_date'])->translatedFormat('d M Y');
                $marked     = $obs['present'] + $obs['absent'];
                $progressPct = (int) round(($obs['present'] / 5) * 100);
                $absenceWarn = $obs['absent'] >= 3
                    ? '<div class="mt-2 rounded-md bg-amber-50 border border-amber-200 px-3 py-2 text-sm text-amber-800"><strong>Check-up flag.</strong> 3 or more absences recorded. Counsellor follow-up will be raised.</div>'
                    : '';

                $headerHtml = <<<HTML
<div class="space-y-3">
  <div class="flex flex-wrap items-center gap-2 text-sm">
    <span class="inline-flex items-center gap-1 rounded-md bg-primary-50 px-2 py-1 font-medium text-primary-700">
      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
      {$startedFmt} to {$endFmt}
    </span>
    <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 font-medium text-emerald-700">Present {$obs['present']}/5</span>
    <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-1 font-medium text-rose-700">Absent {$obs['absent']}</span>
    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 font-medium text-gray-700">Pending {$obs['pending']}</span>
  </div>
  <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200">
    <div class="h-full rounded-full bg-emerald-500 transition-all" style="width: {$progressPct}%"></div>
  </div>
  <div class="text-xs text-gray-600">Stage auto-advances when <strong>5 present days</strong> are recorded ({$marked} of 5 days marked).</div>
  {$absenceWarn}
</div>
HTML;

                $dayFields = [];
                foreach ($obs['rows'] as $i => $row) {
                    $i_label   = $i + 1;
                    $dateFmt   = \Illuminate\Support\Carbon::parse($row['date'])->translatedFormat('D');
                    $dateFmt2  = \Illuminate\Support\Carbon::parse($row['date'])->translatedFormat('d M');
                    $statusBadge = $row['status'] === 'present'
                        ? '<span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">Present</span>'
                        : ($row['status'] === 'absent'
                            ? '<span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2 py-0.5 text-xs font-medium text-rose-700">Absent</span>'
                            : '<span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">Pending</span>');
                    $byLine = $row['by']
                        ? '<div class="mt-1 truncate text-[11px] text-gray-500" title="'.e($row['by']).'">' . e($row['by']) . '</div>'
                        : '<div class="mt-1 text-[11px] italic text-gray-400">Not marked</div>';
                    $headerHtmlDay = <<<HTML
<div class="rounded-md border border-gray-200 bg-gray-50 px-2 py-1.5 text-center">
  <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Day {$i_label}</div>
  <div class="text-sm font-bold text-gray-900">{$dateFmt}</div>
  <div class="text-xs text-gray-600">{$dateFmt2}</div>
  <div class="mt-1">{$statusBadge}</div>
  {$byLine}
</div>
HTML;

                    $dayFields[] = Forms\Components\Group::make([
                        Forms\Components\Placeholder::make("day_{$i}_header")
                            ->label('')
                            ->content(new \Illuminate\Support\HtmlString($headerHtmlDay)),
                        Forms\Components\Select::make("day_{$i}_status")
                            ->label('')
                            ->options([
                                'pending' => '— Pending',
                                'present' => '✓ Present',
                                'absent'  => '✗ Absent',
                            ])
                            ->required()
                            ->native(false),
                    ])->columnSpan(1);
                }

                return [
                    Forms\Components\Hidden::make('start_date'),
                    Forms\Components\Placeholder::make('header')
                        ->label('')
                        ->content(new \Illuminate\Support\HtmlString($headerHtml)),
                    Forms\Components\Section::make('Daily attendance')
                        ->description('Teacher records appear under each date. Use the select to override a day — changes are logged as Principal override.')
                        ->schema([
                            Forms\Components\Grid::make([
                                'default' => 1,
                                'sm'      => 2,
                                'md'      => 5,
                                'lg'      => 5,
                                'xl'      => 5,
                            ])->schema($dayFields),
                        ]),
                ];
            })
            ->action(function (array $arguments, array $data) {
                $a = Application::find($arguments['id']);
                if (! $a) return;
                $meta = $a->meta ?? [];
                $days = data_get($meta, 'observation.days', []);

                // Modal is read-only when no window is set — nothing to save.
                if (! $days) {
                    Notification::make()->title('No observation window set')
                        ->body('Click “Observation Configuration” at the top of this page to set it.')
                        ->warning()->send();
                    return;
                }

                // Override mode: update each day from the form (principal override).
                $i = 0;
                $changed = 0;
                foreach (array_keys($days) as $date) {
                    $status = $data["day_{$i}_status"] ?? 'pending';
                    $prev = $days[$date]['present'] ?? null;
                    $next = match ($status) { 'present' => true, 'absent' => false, default => null };
                    if ($prev !== $next) {
                        $days[$date] = [
                            'present' => $next,
                            'by'      => $next === null ? null : 'Principal override',
                            'at'      => $next === null ? null : now()->toIso8601String(),
                        ];
                        $changed++;
                    }
                    $i++;
                }
                $meta['observation']['days'] = $days;
                $a->meta = $meta;
                $a->save();

                $presentCount = collect($days)->filter(fn ($d) => (bool) ($d['present'] ?? false))->count();
                Notification::make()
                    ->title($changed ? 'Attendance updated' : 'No changes')
                    ->body("Present {$presentCount}/" . count($days) . " days recorded.")
                    ->success()->send();
            });
    }

    public function ecaEbooksAction(): Action
    {
        return Action::make('ecaEbooks')
            ->label('CCA + e-Books')
            ->icon('heroicon-m-sparkles')->color('warning')->size('sm')
            ->modalWidth('3xl')
            ->modalDescription('CCA is free-form text. e-Book pack is picked from the catalog and snapshotted onto the application.')
            ->form(function (array $arguments) {
                $a = Application::find($arguments['id']);
                $unitSlug = $a->campus; // Application.campus stores unit slug (see note above)

                $packs = EbookPack::query()
                    ->where('is_active', true)
                    ->where('unit', $unitSlug)
                    ->where(function ($q) use ($a) {
                        $q->whereNull('grade')->orWhere('grade', $a->grade);
                    })
                    ->orderByRaw('grade IS NULL')
                    ->orderBy('name')
                    ->get();

                return [
                    Forms\Components\TextInput::make('cca')
                        ->label('CCA / ECA choices')
                        ->placeholder('e.g. Robotics, Choir, Basketball')
                        ->required(),

                    Forms\Components\Select::make('ebook_pack_id')
                        ->label('e-Book pack')
                        ->options($packs->pluck('name', 'id'))
                        ->required()
                        ->live()
                        ->helperText($packs->isEmpty()
                            ? 'No active e-Book pack for this cohort. Add one in Enrollment, e-Book Catalog.'
                            : null)
                        ->disabled($packs->isEmpty()),

                    Forms\Components\Placeholder::make('pack_preview')
                        ->label('Included links')
                        ->visible(fn (Forms\Get $get) => (bool) $get('ebook_pack_id'))
                        ->content(function (Forms\Get $get) {
                            $pack = EbookPack::find($get('ebook_pack_id'));
                            if (! $pack) return '—';
                            $platformNames = EbookPlatform::query()
                                ->whereIn('id', collect($pack->items ?? [])->pluck('platform_id')->filter()->unique())
                                ->pluck('name', 'id');
                            $rows = collect($pack->items ?? [])->map(function ($it) use ($platformNames) {
                                $platform = $platformNames[$it['platform_id'] ?? null] ?? '—';
                                return '• ' . ($it['title'] ?? '—') . ' · ' . $platform;
                            })->implode("\n");
                            return new \Illuminate\Support\HtmlString(
                                '<pre class="text-xs whitespace-pre-wrap font-mono">' . e($rows) . '</pre>'
                            );
                        }),
                ];
            })
            ->action(function (array $arguments, array $data) {
                $a = Application::find($arguments['id']);
                if (! $a) return;
                $pack = EbookPack::with('defaultPlatform')->find($data['ebook_pack_id']);
                if (! $pack) {
                    Notification::make()->title('e-Book pack not found')->danger()->send();
                    return;
                }
                $meta = $a->meta ?? [];
                $meta['eca'] = [
                    'choices'      => $data['cca'],
                    'submitted_at' => now()->toIso8601String(),
                ];
                $meta['ebooks'] = [
                    'pack_id'          => $pack->id,
                    'pack_name'        => $pack->name,
                    'default_platform' => $pack->defaultPlatform?->name,
                    'items'            => $pack->items,
                    'assigned_at'      => now()->toIso8601String(),
                ];
                $a->meta = $meta;
                $a->save();
                Notification::make()->title('CCA + e-Books saved')
                    ->body($pack->name . ' · ' . count($pack->items ?? []) . ' links')
                    ->success()->send();
            });
    }

    /**
     * Build the observation view-model: start date, 5 day rows with status,
     * present/absent/pending tallies, and computed end date.
     *
     * @return array{started:bool, start_date:?string, end_date:?string, rows:array<int, array{date:string, status:string, by:?string}>, present:int, absent:int, pending:int}
     */
    public static function observationState(Application $a): array
    {
        $meta = $a->meta ?? [];
        $start = data_get($meta, 'observation.start_date');
        $days  = data_get($meta, 'observation.days', []);

        if (! $start || ! is_array($days) || ! $days) {
            return ['started' => false, 'start_date' => null, 'end_date' => null, 'rows' => [], 'present' => 0, 'absent' => 0, 'pending' => 5];
        }

        $rows = [];
        $present = $absent = $pending = 0;
        foreach ($days as $date => $row) {
            $p = $row['present'] ?? null;
            $status = $p === true ? 'present' : ($p === false ? 'absent' : 'pending');
            $status === 'present' ? $present++ : ($status === 'absent' ? $absent++ : $pending++);
            $rows[] = ['date' => $date, 'status' => $status, 'by' => $row['by'] ?? null];
        }
        $endDate = end($rows)['date'] ?? $start;

        return [
            'started'    => true,
            'start_date' => $start,
            'end_date'   => $endDate,
            'rows'       => $rows,
            'present'    => $present,
            'absent'     => $absent,
            'pending'    => $pending,
        ];
    }

    public function issueStudentIdAction(): Action
    {
        return Action::make('issueStudentId')
            ->label('Issue Student ID / VA')
            ->icon('heroicon-m-identification')->color('success')->size('sm')
            ->modalHeading('Issue Student ID & generate Virtual Account')
            ->modalDescription('The BCA VA is auto-composed from the student ID. You only enter the numeric Student ID — the bank prefix (14339), unit digit and purpose digit are added automatically.')
            ->modalWidth('lg')
            ->fillForm(function (array $arguments) {
                $a = Application::find($arguments['id']);
                if (! $a) return [];
                // Suggest a numeric NIS: YY + unitDigit + zero-padded app-id (7 digits)
                $unitDigit = \App\Support\VirtualAccount::unitDigit($a->campus) ?? '0';
                $suggested = now()->format('y') . $unitDigit . str_pad((string) $a->id, 7, '0', STR_PAD_LEFT);
                return ['student_no' => $suggested];
            })
            ->form(function (array $arguments) {
                $a = Application::find($arguments['id']);
                $unit       = $a?->campus;
                $unitDigit  = \App\Support\VirtualAccount::unitDigit($unit);
                $purpose    = 'tuition';
                $purposeDig = \App\Support\VirtualAccount::purposeDigit($purpose);
                $prefix     = \App\Support\VirtualAccount::BANK_PREFIX;

                return [
                    Forms\Components\Placeholder::make('scheme')
                        ->label('VA scheme')
                        ->content("BCA  ·  {$prefix} + {$unitDigit} (".strtoupper((string)$unit).") + {$purposeDig} (tuition) + <Student ID>"),

                    Forms\Components\TextInput::make('student_no')
                        ->label('Student ID (numeric)')
                        ->required()
                        ->rule('regex:/^\d{6,12}$/')
                        ->helperText('6–12 digits. This becomes the reference portion of the VA.')
                        ->unique(table: 'applications', column: 'assigned_student_no', ignorable: $a)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, Forms\Set $set) use ($prefix, $unitDigit, $purposeDig) {
                            $ref = preg_replace('/\D/', '', (string) $state);
                            $set('va_preview', $ref ? "{$prefix} {$unitDigit} {$purposeDig} {$ref}" : '—');
                        }),

                    Forms\Components\Placeholder::make('va_preview')
                        ->label('Generated VA')
                        ->content(fn (Forms\Get $get) => (function () use ($get, $prefix, $unitDigit, $purposeDig) {
                            $ref = preg_replace('/\D/', '', (string) $get('student_no'));
                            return $ref ? "{$prefix} {$unitDigit} {$purposeDig} {$ref}" : '—';
                        })()),
                ];
            })
            ->action(function (array $arguments, array $data) {
                $a = Application::find($arguments['id']);
                if (! $a) return;

                $nis = preg_replace('/\D/', '', (string) $data['student_no']);
                $va  = \App\Support\VirtualAccount::compose($a->campus, 'tuition', $nis);

                $a->assigned_student_no = $nis;
                $a->status              = 'id_issued';
                $a->va_number           = $va;
                $a->va_purpose          = 'tuition';
                $a->va_issued_at        = now();
                $a->va_issued_by        = optional(auth()->user())->name ?? 'Unit Head';
                // Tuition VAs are open-ended (no 24h cutoff like enrollment); clear any prior expiry.
                $a->va_expires_at       = null;
                $a->save();

                \App\Models\AuditLog::query()->create([
                    'actor'   => optional(auth()->user())->name ?? 'system',
                    'action'  => 'student.id.issued',
                    'subject' => 'Application#'.$a->id,
                    'meta'    => ['student_no' => $nis, 'va_number' => $va],
                ]);

                Notification::make()->title('Student ID issued')
                    ->body("NIS: {$nis}  ·  VA: ".\App\Support\VirtualAccount::format($va))
                    ->success()->send();
            });
    }

    public function confirmTuitionAction(): Action
    {
        return Action::make('confirmTuition')
            ->label('Confirm Tuition Paid → Activate')
            ->icon('heroicon-m-check-badge')->color('success')->size('sm')
            ->requiresConfirmation()
            ->modalDescription('This marks the student as fully activated and ends the onboarding flow.')
            ->action(function (array $arguments) {
                $a = Application::find($arguments['id']);
                if (! $a) return;
                $meta = $a->meta ?? [];
                $meta['tuition'] = ['paid_at' => now()->toIso8601String()];
                $a->meta = $meta;
                $a->status = 'activated';
                $a->save();
                Notification::make()->title('Student activated')->body("{$a->name} is now fully onboarded.")->success()->send();
            });
    }

    /* -------- Cross-tab status moves -------- */

    public function moveToWaitlistAction(): Action
    {
        return Action::make('moveToWaitlist')
            ->label('Move to Waitlist')
            ->icon('heroicon-m-clock')->color('warning')->size('sm')->requiresConfirmation()
            ->action(function (array $arguments) {
                $a = Application::find($arguments['id']);
                if (! $a) return;
                $a->update(['status' => 'waitlisted', 'waitlisted' => true]);
                Notification::make()->title("{$a->name} moved to Waitlist")->warning()->send();
            });
    }

    public function withdrawAction(): Action
    {
        return Action::make('withdraw')
            ->label('Withdraw')
            ->icon('heroicon-m-arrow-uturn-left')->color('danger')->size('sm')
            ->form([Forms\Components\Textarea::make('reason')->label('Reason')->rows(3)->required()])
            ->action(function (array $arguments, array $data) {
                $a = Application::find($arguments['id']);
                if (! $a) return;
                $a->update(['status' => 'withdrawn', 'decline_reason' => $data['reason']]);
                Notification::make()->title("{$a->name} withdrawn")->danger()->send();
            });
    }

    public function reinstateAction(): Action
    {
        return Action::make('reinstate')
            ->label('Reinstate to Accepted')
            ->icon('heroicon-m-arrow-path')->color('success')->size('sm')->requiresConfirmation()
            ->action(function (array $arguments) {
                $a = Application::find($arguments['id']);
                if (! $a) return;
                $a->update(['status' => 'accepted', 'waitlisted' => false]);
                Notification::make()->title("{$a->name} moved back to Accepted")->success()->send();
            });
    }
}
