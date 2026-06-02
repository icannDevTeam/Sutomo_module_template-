<?php

namespace App\Filament\Principal\Pages;

use App\Models\DutyAssignment;
use App\Models\LeaveType;
use App\Models\Teacher;
use App\Models\TeacherLeave;
use App\Support\SubstituteBroadcaster;
use App\Support\SubstituteSuggester;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;

class SubmitLeaveOnBehalf extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-date-range';
    protected static ?string $navigationGroup = 'Approvals';
    protected static ?int $navigationSort = 1;
    protected static ?string $title = 'New Leave Application';
    protected static ?string $navigationLabel = 'New Leave Application';
    protected static ?string $slug = 'submit-leave';
    protected static string $view = 'filament.principal.pages.submit-leave-on-behalf';
    protected static bool $shouldRegisterNavigation = false;

    #[Url(as: 't')]
    public ?int $teacherId = null;

    #[Url(as: 'm')]
    public ?string $monthKey = null;  // YYYY-MM

    public string $type = 'sick';
    public ?string $startsAt = null;
    public ?string $endsAt = null;
    public ?string $reason = null;
    public bool $autoSearchEnabled = true;

    /** Optional: principal pre-picks a substitute → fast-path approve. */
    public ?int $preferredSubstituteId = null;

    /** When pre-picking, also create a DutyAssignment for the substitute. */
    public bool $createDutyAssignment = true;

    /** Banner shown after a successful submit. */
    public ?array $lastSubmitted = null;

    public function dismissLastSubmitted(): void
    {
        $this->lastSubmitted = null;
    }

    public function mount(): void
    {
        $this->monthKey = $this->monthKey ?: now()->format('Y-m');
        // Default type = first active leave type
        $first = LeaveType::query()->where('is_active', true)->orderBy('sort_order')->first();
        if ($first) $this->type = $first->key;
    }

    public function getViewData(): array
    {
        $teachers = Teacher::query()
            ->whereNotIn('status', ['alumni'])
            ->orderBy('name')
            ->get(['id', 'name', 'subject', 'campus', 'quota']);

        $teacher = $this->teacherId ? $teachers->firstWhere('id', $this->teacherId) : null;

        $month = Carbon::createFromFormat('Y-m', $this->monthKey)->startOfMonth();
        $prev = $month->copy()->subMonth()->format('Y-m');
        $next = $month->copy()->addMonth()->format('Y-m');

        // Build calendar grid
        $firstDow = (int) $month->copy()->startOfMonth()->dayOfWeekIso; // 1=Mon..7=Sun
        $daysIn   = $month->daysInMonth;
        $cells = [];
        for ($i = 1; $i < $firstDow; $i++) $cells[] = null;
        for ($d = 1; $d <= $daysIn; $d++) {
            $cells[] = $month->copy()->day($d);
        }
        while (count($cells) % 7 !== 0) $cells[] = null;

        // Existing leaves for this teacher
        $leaves = collect();
        if ($teacher) {
            $leaves = TeacherLeave::where('teacher_id', $teacher->id)
                ->whereIn('status', ['approved', 'pending'])
                ->get();
        }

        $leavesByDate = [];
        foreach ($leaves as $l) {
            if (! $l->starts_at || ! $l->ends_at) continue;
            $cursor = $l->starts_at->copy();
            while ($cursor->lte($l->ends_at)) {
                $leavesByDate[$cursor->toDateString()][] = $l;
                $cursor->addDay();
            }
        }

        // Single quota card
        $quotas = [];
        if ($teacher) {
            $limit = $teacher->quotaFor();
            $used  = $teacher->leaveDaysUsed($month->year);
            $pct   = $limit > 0 ? min(100, ($used / $limit) * 100) : 0;
            $quotas[] = [
                'label'     => 'Leave quota',
                'used'      => $used,
                'limit'     => $limit,
                'remaining' => max(0, $limit - $used),
                'pct'       => $pct,
                'color'     => $used >= $limit ? 'danger' : ($pct >= 75 ? 'warning' : 'success'),
            ];
        }

        // Selected day count (excluding weekends)
        $workingDays = 0;
        if ($this->startsAt && $this->endsAt) {
            try {
                $s = Carbon::parse($this->startsAt);
                $e = Carbon::parse($this->endsAt);
                if ($e->gte($s)) {
                    $c = $s->copy();
                    while ($c->lte($e)) {
                        if (! in_array($c->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
                            $workingDays++;
                        }
                        $c->addDay();
                    }
                }
            } catch (\Throwable $e) {}
        }

        return [
            'teachers'      => $teachers,
            'teacher'       => $teacher,
            'month'         => $month,
            'monthLabel'    => $month->format('F Y'),
            'prevMonth'     => $prev,
            'nextMonth'     => $next,
            'cells'         => $cells,
            'leavesByDate'  => $leavesByDate,
            'quotas'        => $quotas,
            'workingDays'   => $workingDays,
            'leaveTypes'    => LeaveType::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('label')
                ->get(['key', 'label', 'affects_quota', 'requires_substitute', 'color']),
            'requiresSubstitute' => $this->requiresSubstitute(),
            'suggestions'        => $this->buildSuggestions($teacher),
            'tierLabels'         => SubstituteSuggester::TIER_LABELS,
            'pickedSubstitute'   => $this->preferredSubstituteId
                ? Teacher::find($this->preferredSubstituteId)
                : null,
            'canSubmit'          => $this->canSubmit(),
        ];
    }

    /** Whether the currently-selected leave type requires substitute coverage. */
    public function requiresSubstitute(): bool
    {
        return LeaveType::requiresSubstitute($this->type);
    }

    /**
     * Build a transient (un-persisted) TeacherLeave from current form state
     * and ask SubstituteSuggester for ranked candidates. Returns empty
     * collection until teacher + dates are picked.
     */
    protected function buildSuggestions(?Teacher $teacher)
    {
        // No substitute needed for this leave type → skip suggestion engine.
        if (! $this->requiresSubstitute()) {
            return collect();
        }
        if (! $teacher || ! $this->startsAt || ! $this->endsAt) {
            return collect();
        }
        try {
            $start = Carbon::parse($this->startsAt);
            $end   = Carbon::parse($this->endsAt);
            if ($end->lt($start)) return collect();
        } catch (\Throwable $e) {
            return collect();
        }

        $transient = new TeacherLeave([
            'teacher_id' => $teacher->id,
            'type'       => $this->type,
            'starts_at'  => $start,
            'ends_at'    => $end,
        ]);
        $transient->setRelation('teacher', $teacher);

        return SubstituteSuggester::for($transient, 12);
    }

    /**
     * Submit gate: leaves require a coverage path — either a picked
     * substitute or auto-broadcast enabled.
     */
    public function canSubmit(): bool
    {
        if (! $this->teacherId || ! $this->startsAt || ! $this->endsAt) {
            return false;
        }
        // Leave types that don't require a substitute can be submitted directly.
        if (! $this->requiresSubstitute()) {
            return true;
        }
        return $this->preferredSubstituteId !== null || $this->autoSearchEnabled;
    }

    public function pickSubstitute(int $teacherId): void
    {
        $this->preferredSubstituteId = $teacherId;
    }

    public function clearPick(): void
    {
        $this->preferredSubstituteId = null;
    }

    public function pickDate(string $date): void
    {
        if (! $this->teacherId) return;

        // First click: set start. Second click: set end (or reset if before start).
        if (! $this->startsAt || ($this->startsAt && $this->endsAt)) {
            $this->startsAt = $date;
            $this->endsAt = $date;
            return;
        }

        if (Carbon::parse($date)->lt(Carbon::parse($this->startsAt))) {
            $this->startsAt = $date;
            $this->endsAt = $date;
            return;
        }

        $this->endsAt = $date;
    }

    public function clearSelection(): void
    {
        $this->startsAt = null;
        $this->endsAt = null;
    }

    /** Snap month-key to whichever month contains the From date so the calendar follows the user. */
    public function updatedStartsAt($value): void
    {
        if (! $value) return;
        try {
            $this->monthKey = Carbon::parse($value)->format('Y-m');
            if ($this->endsAt && Carbon::parse($this->endsAt)->lt(Carbon::parse($value))) {
                $this->endsAt = $value;
            }
        } catch (\Throwable $e) {}
    }

    public function updatedEndsAt($value): void
    {
        if (! $value || ! $this->startsAt) return;
        try {
            if (Carbon::parse($value)->lt(Carbon::parse($this->startsAt))) {
                $this->endsAt = $this->startsAt;
            }
        } catch (\Throwable $e) {}
    }

    public function submit(): void
    {
        if (! $this->teacherId || ! $this->startsAt || ! $this->endsAt) {
            Notification::make()->title('Pick a teacher and a date range first.')->danger()->send();
            return;
        }

        $teacher = Teacher::find($this->teacherId);
        if (! $teacher) return;

        // ── No-substitute path: leave types like Brief Absence / Assigned Work ──
        if (! $this->requiresSubstitute()) {
            $leave = TeacherLeave::create([
                'teacher_id'          => $teacher->id,
                'type'                => $this->type,
                'starts_at'           => $this->startsAt,
                'ends_at'             => $this->endsAt,
                'reason'              => $this->reason ?: 'Filed by principal on behalf of teacher.',
                'status'              => 'approved',
                'auto_search_enabled' => false,
                'decided_by'          => auth()->user()?->name ?? 'Principal',
                'decided_at'          => now(),
            ]);

            $this->lastSubmitted = [
                'teacher'    => $teacher->name,
                'type'       => LeaveType::labelFor($this->type),
                'dates'      => Carbon::parse($this->startsAt)->format('d M').' – '.Carbon::parse($this->endsAt)->format('d M Y'),
                'status'     => 'approved',
                'substitute' => null,
                'duty'       => false,
                'message'    => 'No substitute required for this type.',
                'leave_id'   => $leave->id,
            ];

            Notification::make()
                ->title('Leave approved for ' . $teacher->name)
                ->body(LeaveType::labelFor($this->type) . ' — no substitute required.')
                ->success()
                ->send();

            $this->reset(['startsAt', 'endsAt', 'reason', 'preferredSubstituteId']);
            return;
        }

        if ($this->preferredSubstituteId === null && ! $this->autoSearchEnabled) {
            Notification::make()
                ->title('Coverage path required')
                ->body('Pick a substitute from the suggestions, or enable auto substitute search.')
                ->danger()
                ->send();
            return;
        }

        // ── Fast-path: principal pre-picked a substitute ─────────────────
        if ($this->preferredSubstituteId !== null) {
            // Defense in depth: re-run suggester and confirm the pick is still assignable.
            $transient = new TeacherLeave([
                'teacher_id' => $teacher->id,
                'type'       => $this->type,
                'starts_at'  => Carbon::parse($this->startsAt),
                'ends_at'    => Carbon::parse($this->endsAt),
            ]);
            $transient->setRelation('teacher', $teacher);
            $candidates = SubstituteSuggester::for($transient, 100);
            $picked = $candidates->firstWhere(
                fn ($c) => (int) $c['teacher']->id === (int) $this->preferredSubstituteId
            );
            if ($picked && ! $picked['is_assignable']) {
                Notification::make()
                    ->title('That substitute is no longer available.')
                    ->body(implode(' · ', $picked['conflict_reasons']))
                    ->danger()
                    ->send();
                return;
            }

            $sub = Teacher::find($this->preferredSubstituteId);
            if (! $sub) {
                Notification::make()->title('Substitute not found.')->danger()->send();
                return;
            }

            $leave = TeacherLeave::create([
                'teacher_id'           => $teacher->id,
                'type'                 => $this->type,
                'starts_at'            => $this->startsAt,
                'ends_at'              => $this->endsAt,
                'reason'               => $this->reason ?: 'Filed by principal on behalf of teacher.',
                'status'               => 'approved',
                'substitute_teacher_id'=> $sub->id,
                'decided_by'           => auth()->user()?->name ?? 'Principal',
                'decided_at'           => now(),
                'auto_search_enabled'  => false,
            ]);

            if ($this->createDutyAssignment) {
                DutyAssignment::create([
                    'teacher_id'    => $sub->id,
                    'title'         => 'Cover for ' . $teacher->name,
                    'starts_at'     => $leave->starts_at,
                    'ends_at'       => $leave->ends_at,
                    'recurrence'    => 'once',
                    'status'        => 'pending',
                    'assigned_by'   => auth()->user()?->name ?? 'Principal',
                    'academic_year' => DutyAssignment::academicYearFor($leave->starts_at),
                ]);
            }

            $this->lastSubmitted = [
                'teacher'    => $teacher->name,
                'type'       => LeaveType::labelFor($this->type),
                'dates'      => Carbon::parse($this->startsAt)->format('d M').' – '.Carbon::parse($this->endsAt)->format('d M Y'),
                'status'     => 'approved',
                'substitute' => $sub->name,
                'duty'       => $this->createDutyAssignment,
                'message'    => $sub->name . ' is now covering.',
                'leave_id'   => $leave->id,
            ];

            Notification::make()
                ->title('Leave approved for ' . $teacher->name)
                ->body($sub->name . ' is now covering · '
                    . ($this->createDutyAssignment ? 'duty hand-off created.' : 'no duty hand-off created.'))
                ->success()
                ->send();

            $this->reset(['startsAt', 'endsAt', 'reason', 'preferredSubstituteId']);
            return;
        }

        // ── Broadcast path: file pending and start auto-search ───────────
        $leave = TeacherLeave::create([
            'teacher_id'          => $this->teacherId,
            'type'                => $this->type,
            'starts_at'           => $this->startsAt,
            'ends_at'             => $this->endsAt,
            'reason'              => $this->reason ?: 'Filed by principal on behalf of teacher.',
            'status'              => 'pending',
            'auto_search_enabled' => true,
        ]);

        $offers = SubstituteBroadcaster::startAutoSearch($leave);
        $invited = count($offers);

        $this->lastSubmitted = [
            'teacher'    => $teacher->name,
            'type'       => LeaveType::labelFor($this->type),
            'dates'      => Carbon::parse($this->startsAt)->format('d M').' – '.Carbon::parse($this->endsAt)->format('d M Y'),
            'status'     => 'pending',
            'substitute' => null,
            'duty'       => false,
            'message'    => $invited
                ? "Auto substitute search started — {$invited} teacher(s) invited."
                : 'No eligible candidates found — assign manually from the leave list.',
            'leave_id'   => $leave->id,
        ];

        Notification::make()
            ->title('Leave submitted for ' . $teacher->name)
            ->body($invited
                ? "Auto substitute search started. {$invited} teacher(s) invited."
                : 'No eligible candidates found — assign manually from the leave list.')
            ->success()
            ->send();

        $this->reset(['startsAt', 'endsAt', 'reason', 'preferredSubstituteId']);
    }
}
