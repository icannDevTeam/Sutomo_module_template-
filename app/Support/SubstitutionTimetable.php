<?php

namespace App\Support;

use App\Models\DutyAssignment;
use App\Models\LeaveType;
use App\Models\Teacher;
use App\Models\TeacherLeave;
use App\Models\TimetableLesson;
use App\Services\Timetable\TeacherSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Read-only join across approved TeacherLeaves + DutyAssignments + the
 * published TimetableLesson grid. Produces a flat list of "cells", one per
 * lesson that needs cover (or per day of a standalone duty), each tagged
 * with a coverage state:
 *
 *   confirmed  — substitute assigned, cover hand-off accepted/completed
 *   awaiting   — substitute assigned, hand-off still pending their reply
 *   no_handoff — substitute assigned, but no cover hand-off recorded
 *   declined   — substitute declined the cover hand-off
 *   uncovered  — leave approved but nobody assigned
 *   duty       — standalone duty (supervision, invigilation, …)
 *
 * Plus an "attention" list of leaves in the window that still need action
 * (including pending leaves that haven't been approved yet).
 */
class SubstitutionTimetable
{
    /** Map TimetableLesson.day → ISO weekday number (1=Mon..6=Sat). */
    public const DAY_TO_ISO = [
        'SENIN'  => 1,
        'SELASA' => 2,
        'RABU'   => 3,
        'KAMIS'  => 4,
        'JUMAT'  => 5,
        'SABTU'  => 6,
    ];

    public const ISO_TO_DAY = [
        1 => 'SENIN',
        2 => 'SELASA',
        3 => 'RABU',
        4 => 'KAMIS',
        5 => 'JUMAT',
        6 => 'SABTU',
    ];

    public const DAY_LABEL = [
        'SENIN'  => 'Mon',
        'SELASA' => 'Tue',
        'RABU'   => 'Wed',
        'KAMIS'  => 'Thu',
        'JUMAT'  => 'Fri',
        'SABTU'  => 'Sat',
    ];

    public const STATES = [
        'confirmed'  => 'Confirmed',
        'awaiting'   => 'Awaiting reply',
        'no_handoff' => 'No hand-off',
        'declined'   => 'Hand-off declined',
        'uncovered'  => 'Uncovered',
        'duty'       => 'Duty',
    ];

    /** States that mean someone has to act. */
    public const ACTION_STATES = ['no_handoff', 'declined', 'uncovered'];

    /**
     * School units. Teachers.campus holds either one of these unit codes or a
     * campus name ("Sutomo 1", "Sutomo 2", …) — there's no separate unit column yet.
     */
    public const UNITS = [
        'tk'  => 'TK',
        'sd'  => 'SD',
        'smp' => 'SMP',
        'sma' => 'SMA',
        'int' => 'International',
    ];

    /** Values accepted by the page's "Show" filter. */
    public const FILTERS = [
        'action'    => 'Needs action',
        'awaiting'  => 'Awaiting reply',
        'confirmed' => 'Confirmed',
        'duty'      => 'Duties only',
    ];

    /**
     * Build the substitution timetable for a window.
     *
     * @return array{
     *   cells: array<int, array<string, mixed>>,
     *   attention: array<int, array<string, mixed>>,
     *   summary: array<string, int>,
     *   days: array<int, array{date:string, day:string, label:string}>,
     *   periods: array<int, string>,
     *   week_start: string,
     *   week_end: string
     * }
     */
    public static function buildWindow(
        CarbonImmutable $start,
        CarbonImmutable $end,
        ?string $campus = null,
        ?string $filter = null,
        ?string $unit = null,
        ?string $search = null,
    ): array {
        $start = $start->startOfDay();
        $end   = $end->endOfDay();

        // Resolve published timetable definition (single school-wide for now).
        $defId = TeacherSchedule::activeDefinitionId();
        $allLessons = $defId
            ? TimetableLesson::query()->where('definition_id', $defId)->get()
            : collect();

        /** @var Collection<string, Collection<string, Collection<int, TimetableLesson>>> $lessonsByTeacher */
        $lessonsByTeacher = $allLessons->groupBy('teacher_ref')->map(
            fn (Collection $rows) => $rows->groupBy('day')
        );

        // ----- Leaves overlapping window (approved → cells, pending → attention) -----
        $leaves = TeacherLeave::query()
            ->whereIn('status', ['approved', 'pending'])
            // whereDate: SQLite stores these as 'Y-m-d H:i:s', so a plain string compare drops boundary days.
            ->whereDate('starts_at', '<=', $end->toDateString())
            ->whereDate('ends_at', '>=', $start->toDateString())
            ->with(['teacher', 'substitute'])
            ->orderBy('starts_at')
            ->get()
            ->filter(fn (TeacherLeave $l) => self::requiresSubstitute($l->type))
            ->filter(fn (TeacherLeave $l) => self::inScope($l->teacher, $campus, $unit)
                || self::inScope($l->substitute, $campus, $unit))
            ->filter(fn (TeacherLeave $l) => self::matchesSearch($search, $l->teacher?->name, $l->substitute?->name))
            ->values();

        // ----- DutyAssignments overlapping window (declined kept for hand-off lookup) -----
        $duties = DutyAssignment::query()
            ->whereIn('status', ['pending', 'accepted', 'completed', 'declined'])
            ->where('starts_at', '<=', $end)
            ->where(function ($q) use ($start) {
                // One-off duties may have no end → treat as single-day.
                $q->where('ends_at', '>=', $start)
                  ->orWhere(fn ($q) => $q->whereNull('ends_at')->where('starts_at', '>=', $start));
            })
            ->with('teacher')
            ->get();

        $cells = [];
        $attention = [];
        $teachersOnLeave = [];
        $handoffDutyIds = [];

        foreach ($leaves as $leave) {
            $original = $leave->teacher;
            if (! $original) {
                continue;
            }
            $sub = $leave->substitute;

            $leaveStart = CarbonImmutable::parse($leave->starts_at)->startOfDay();
            $leaveEnd   = CarbonImmutable::parse($leave->ends_at)->endOfDay();
            $iterStart  = $leaveStart->greaterThan($start) ? $leaveStart : $start;
            $iterEnd    = $leaveEnd->lessThan($end) ? $leaveEnd : $end;

            $teacherDayLessons = $lessonsByTeacher->get($original->code);
            $lessonCount = self::countLessons($teacherDayLessons, $iterStart, $iterEnd);

            if ($leave->status === 'pending') {
                $attention[] = self::attentionRow($leave, 'pending', $lessonCount);
                continue;
            }

            $teachersOnLeave[$original->id] = true;

            // The cover hand-off is a one-off duty for the substitute overlapping the leave.
            $handoff = $sub ? self::findHandoff($duties, $sub->id, $leaveStart, $leaveEnd) : null;
            if ($handoff) {
                $handoffDutyIds[$handoff->id] = true;
            }

            $state = match (true) {
                ! $sub                                      => 'uncovered',
                ! $handoff                                  => 'no_handoff',
                $handoff->status === 'declined'             => 'declined',
                in_array($handoff->status, ['accepted', 'completed'], true) => 'confirmed',
                default                                     => 'awaiting',
            };

            if (in_array($state, self::ACTION_STATES, true)) {
                $attention[] = self::attentionRow($leave, $state, $lessonCount);
            }

            $cursor = $iterStart;
            while ($cursor->lessThanOrEqualTo($iterEnd)) {
                $iso = (int) $cursor->isoWeekday();
                $dayKey = self::ISO_TO_DAY[$iso] ?? null;
                $lessons = $dayKey ? ($teacherDayLessons?->get($dayKey) ?? collect()) : collect();

                foreach ($lessons as $lesson) {
                    $cells[] = [
                        'date'          => $cursor->toDateString(),
                        'day'           => $dayKey,
                        'day_label'     => self::DAY_LABEL[$dayKey],
                        'period'        => $lesson->period,
                        'session'       => $lesson->session,
                        'class_code'    => $lesson->class_code,
                        'subject'       => $lesson->subject,
                        'room'          => $lesson->room,
                        'original_id'   => $original->id,
                        'original'      => $original->name,
                        'substitute_id' => $sub?->id,
                        'substitute'    => $sub?->name,
                        'source'        => 'leave',
                        'leave_id'      => $leave->id,
                        'leave_type'    => $leave->type,
                        'duty_id'       => $handoff?->id,
                        'state'         => $state,
                        'status'        => $handoff?->status ?? ($sub ? 'none' : 'uncovered'),
                        'gap'           => in_array($state, self::ACTION_STATES, true),
                        'gap_reason'    => self::gapReason($state),
                    ];
                }
                $cursor = $cursor->addDay();
            }
        }

        // ----- Standalone duties (everything that isn't a cover hand-off) -----
        foreach ($duties as $duty) {
            $teacher = $duty->teacher;
            if (! $teacher || isset($handoffDutyIds[$duty->id])) {
                continue;
            }
            if (! in_array($duty->status, ['pending', 'accepted'], true)) {
                continue;
            }
            if (! self::inScope($teacher, $campus, $unit)
                || ! self::matchesSearch($search, $teacher->name, $duty->title)) {
                continue;
            }

            $dStart = CarbonImmutable::instance($duty->starts_at)->startOfDay();
            $dEnd   = $duty->ends_at
                ? CarbonImmutable::instance($duty->ends_at)->endOfDay()
                : $dStart->endOfDay();
            $iterStart = $dStart->greaterThan($start) ? $dStart : $start;
            $iterEnd   = $dEnd->lessThan($end) ? $dEnd : $end;

            $weeklyDays = array_map('intval', (array) ($duty->days_of_week ?? []));

            $cursor = $iterStart;
            while ($cursor->lessThanOrEqualTo($iterEnd)) {
                $iso = (int) $cursor->isoWeekday();
                $emit = $iso <= 6 && ($duty->recurrence !== 'weekly' || ! $weeklyDays || in_array($iso, $weeklyDays, true));
                if ($emit) {
                    $dayKey = self::ISO_TO_DAY[$iso];
                    $cells[] = [
                        'date'          => $cursor->toDateString(),
                        'day'           => $dayKey,
                        'day_label'     => self::DAY_LABEL[$dayKey],
                        'period'        => '—',
                        'session'       => null,
                        'class_code'    => null,
                        'subject'       => $duty->title,
                        'room'          => $duty->location,
                        'time'          => self::timeRange($duty),
                        'original_id'   => null,
                        'original'      => null,
                        'substitute_id' => $teacher->id,
                        'substitute'    => $teacher->name,
                        'source'        => 'duty',
                        'leave_id'      => null,
                        'leave_type'    => null,
                        'duty_id'       => $duty->id,
                        'state'         => 'duty',
                        'status'        => $duty->status,
                        'gap'           => false,
                        'gap_reason'    => null,
                    ];
                }
                $cursor = $cursor->addDay();
            }
        }

        // Summary is computed before filtering so KPIs always reflect the full window.
        $leaveCells = array_filter($cells, fn ($c) => $c['source'] === 'leave');
        $countState = fn (string $s) => count(array_filter($leaveCells, fn ($c) => $c['state'] === $s));
        $summary = [
            'teachers_on_leave' => count($teachersOnLeave),
            'lessons_total'     => count($leaveCells),
            'confirmed'         => $countState('confirmed'),
            'awaiting'          => $countState('awaiting'),
            'no_handoff'        => $countState('no_handoff') + $countState('declined'),
            'uncovered'         => $countState('uncovered'),
            'gaps_count'        => count(array_filter($leaveCells, fn ($c) => $c['gap'])),
            'duties_count'      => count(array_filter($cells, fn ($c) => $c['source'] === 'duty')),
            'pending_leaves'    => count(array_filter($attention, fn ($a) => $a['state'] === 'pending')),
            // Back-compat with older callers.
            'periods_covered'   => $countState('confirmed') + $countState('awaiting'),
        ];

        if ($filter && isset(self::FILTERS[$filter])) {
            $cells = array_values(array_filter($cells, fn ($c) => match ($filter) {
                'action' => $c['gap'],
                default  => $c['state'] === $filter,
            }));
        }

        $periodOrder = array_flip(TeacherSchedule::PERIODS);
        usort($cells, function ($a, $b) use ($periodOrder) {
            return [$a['date'], $a['source'] === 'duty' ? 0 : 1, $a['session'] ?? '', $periodOrder[$a['period']] ?? 99, $a['class_code'] ?? '']
                <=> [$b['date'], $b['source'] === 'duty' ? 0 : 1, $b['session'] ?? '', $periodOrder[$b['period']] ?? 99, $b['class_code'] ?? ''];
        });

        // Attention: worst first, then soonest.
        $severity = ['uncovered' => 0, 'declined' => 1, 'no_handoff' => 2, 'pending' => 3];
        usort($attention, fn ($a, $b) => [$severity[$a['state']] ?? 9, $a['starts_at']] <=> [$severity[$b['state']] ?? 9, $b['starts_at']]);

        // Day axis covering the window (Mon..Sat only).
        $days = [];
        $cursor = $start;
        while ($cursor->lessThanOrEqualTo($end)) {
            $iso = (int) $cursor->isoWeekday();
            if ($iso <= 6) {
                $key = self::ISO_TO_DAY[$iso];
                $days[] = [
                    'date'  => $cursor->toDateString(),
                    'day'   => $key,
                    'label' => self::DAY_LABEL[$key],
                ];
            }
            $cursor = $cursor->addDay();
        }

        // Distinct periods present in the active timetable (fallback to defaults).
        $periods = $allLessons->pluck('period')->unique()->values()->all();
        if (empty($periods)) {
            $periods = TeacherSchedule::PERIODS;
        } else {
            usort($periods, fn ($a, $b) => ($periodOrder[$a] ?? 99) <=> ($periodOrder[$b] ?? 99));
        }

        return [
            'cells'      => $cells,
            'attention'  => $attention,
            'summary'    => $summary,
            'days'       => $days,
            'periods'    => $periods,
            'week_start' => $start->toDateString(),
            'week_end'   => $end->toDateString(),
        ];
    }

    /** Campus names (e.g. "Sutomo 1") present on teachers, for the Campus dropdown. */
    public static function availableCampuses(): array
    {
        return self::distinctTeacherCampusValues()
            ->reject(fn ($v) => isset(self::UNITS[strtolower($v)]))
            ->values()
            ->all();
    }

    /** Units present on teachers, as code => label, in school order. */
    public static function availableUnits(): array
    {
        $present = self::distinctTeacherCampusValues()->map(fn ($v) => strtolower($v))->all();

        return array_filter(self::UNITS, fn ($k) => in_array($k, $present, true), ARRAY_FILTER_USE_KEY);
    }

    protected static function distinctTeacherCampusValues(): Collection
    {
        return Teacher::query()
            ->whereNotNull('campus')
            ->where('campus', '!=', '')
            ->distinct()
            ->orderBy('campus')
            ->pluck('campus');
    }

    protected static function inScope(?Teacher $t, ?string $campus, ?string $unit): bool
    {
        if (! $campus && ! $unit) {
            return true;
        }
        if (! $t) {
            return false;
        }
        $value = (string) $t->campus;

        return (! $campus || $value === $campus)
            && (! $unit || strtolower($value) === strtolower($unit));
    }

    protected static function matchesSearch(?string $search, ?string ...$haystacks): bool
    {
        if ($search === null || $search === '') {
            return true;
        }
        foreach ($haystacks as $h) {
            if ($h !== null && mb_stripos($h, $search) !== false) {
                return true;
            }
        }
        return false;
    }

    public static function gapReason(string $state): ?string
    {
        return match ($state) {
            'uncovered'  => 'No substitute assigned',
            'no_handoff' => 'No duty hand-off recorded',
            'declined'   => 'Substitute declined the hand-off',
            default      => null,
        };
    }

    protected static function requiresSubstitute(?string $type): bool
    {
        static $cache = [];
        $key = (string) $type;
        return $cache[$key] ??= LeaveType::requiresSubstitute($type);
    }

    /**
     * A cover hand-off is a one-off duty for the substitute that overlaps the
     * leave. Weekly duties (supervision etc.) are never treated as hand-offs.
     * Prefers an active hand-off over a declined one.
     */
    protected static function findHandoff(Collection $duties, int $subId, CarbonImmutable $from, CarbonImmutable $to): ?DutyAssignment
    {
        $matches = $duties->filter(function (DutyAssignment $d) use ($subId, $from, $to) {
            if ((int) $d->teacher_id !== $subId || $d->recurrence === 'weekly') {
                return false;
            }
            $dEnd = $d->ends_at ?? $d->starts_at;
            return $d->starts_at <= $to && CarbonImmutable::instance($dEnd)->endOfDay() >= $from;
        });

        return $matches->first(fn ($d) => $d->status !== 'declined') ?? $matches->first();
    }

    protected static function countLessons(?Collection $teacherDayLessons, CarbonImmutable $from, CarbonImmutable $to): int
    {
        if (! $teacherDayLessons) {
            return 0;
        }
        $n = 0;
        for ($c = $from->startOfDay(); $c->lessThanOrEqualTo($to); $c = $c->addDay()) {
            $dayKey = self::ISO_TO_DAY[(int) $c->isoWeekday()] ?? null;
            if ($dayKey) {
                $n += $teacherDayLessons->get($dayKey)?->count() ?? 0;
            }
        }
        return $n;
    }

    protected static function attentionRow(TeacherLeave $leave, string $state, int $lessons): array
    {
        return [
            'leave_id'   => $leave->id,
            'teacher'    => $leave->teacher?->name,
            'campus'     => $leave->teacher?->campus,
            'type_label' => LeaveType::labelFor($leave->type),
            'starts_at'  => $leave->starts_at->toDateString(),
            'ends_at'    => $leave->ends_at->toDateString(),
            'substitute' => $leave->substitute?->name,
            'state'      => $state,
            'label'      => $state === 'pending'
                ? ($leave->substitute_teacher_id ? 'Pending approval' : 'Pending · no substitute yet')
                : self::gapReason($state),
            'lessons'    => $lessons,
        ];
    }

    protected static function timeRange(DutyAssignment $duty): ?string
    {
        $s = $duty->starts_at?->format('H:i');
        $e = $duty->ends_at?->format('H:i');
        if (! $s || ($s === '00:00' && (! $e || $e === '00:00'))) {
            return null;
        }
        return $e && $e !== $s ? "{$s}–{$e}" : $s;
    }
}
