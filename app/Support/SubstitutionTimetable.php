<?php

namespace App\Support;

use App\Models\DutyAssignment;
use App\Models\Teacher;
use App\Models\TeacherLeave;
use App\Models\TimetableLesson;
use App\Services\Timetable\TeacherSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Read-only join across approved TeacherLeaves + DutyAssignments + the
 * published TimetableLesson grid. Produces a flat list of "covered cell"
 * rows describing every period that is currently being covered by a
 * substitute, plus gap rows where an approved leave overlaps a regular
 * lesson but no DutyAssignment has been recorded.
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

    /**
     * Build the substitution timetable for a window.
     *
     * @return array{
     *   cells: array<int, array<string, mixed>>,
     *   summary: array{teachers_on_leave:int, periods_covered:int, gaps_count:int, duties_count:int},
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
        ?string $statusFilter = null,
    ): array {
        $start = $start->startOfDay();
        $end   = $end->endOfDay();

        // Resolve published timetable definition (single school-wide for now).
        $defId = TeacherSchedule::activeDefinitionId();
        $allLessons = $defId
            ? TimetableLesson::query()->where('definition_id', $defId)->get()
            : collect();

        // Index lessons by teacher_ref → keyed by day.
        /** @var Collection<string, Collection<string, Collection<int, TimetableLesson>>> $lessonsByTeacher */
        $lessonsByTeacher = $allLessons->groupBy('teacher_ref')->map(
            fn (Collection $rows) => $rows->groupBy('day')
        );

        // Resolve teacher_ref → Teacher for campus filtering and labels.
        $teacherRefs = $allLessons->pluck('teacher_ref')->filter()->unique()->values();
        $teachersByRef = Teacher::query()->whereIn('code', $teacherRefs)->get()->keyBy('code');

        // ----- Approved leaves overlapping window -----
        $leaves = TeacherLeave::query()
            ->where('status', 'approved')
            ->whereNotNull('substitute_teacher_id')
            ->where('starts_at', '<=', $end->toDateString())
            ->where('ends_at', '>=', $start->toDateString())
            ->with(['teacher', 'substitute'])
            ->get();

        // ----- Active DutyAssignments overlapping window -----
        $duties = DutyAssignment::query()
            ->whereIn('status', ['pending', 'accepted'])
            ->where('starts_at', '<=', $end)
            ->where('ends_at', '>=', $start)
            ->with('teacher')
            ->get();

        $cells = [];
        $teachersOnLeave = [];

        // Walk leaves and emit lesson-level coverage rows + gap detection.
        foreach ($leaves as $leave) {
            $original = $leave->teacher;
            $sub      = $leave->substitute;
            if (! $original || ! $sub) {
                continue;
            }
            if ($campus && $original->campus !== $campus && $sub->campus !== $campus) {
                continue;
            }

            $teachersOnLeave[$original->id] = $original->name;

            $leaveStart = CarbonImmutable::parse($leave->starts_at)->startOfDay();
            $leaveEnd   = CarbonImmutable::parse($leave->ends_at)->endOfDay();

            // Effective overlap with the requested window.
            $iterStart = $leaveStart->greaterThan($start) ? $leaveStart : $start;
            $iterEnd   = $leaveEnd->lessThan($end) ? $leaveEnd : $end;

            $teacherDayLessons = $lessonsByTeacher->get($original->code);

            // Has any matching duty been logged for this leave window?
            $hasDuty = $duties->contains(function (DutyAssignment $d) use ($leave, $sub) {
                return (int) $d->teacher_id === (int) $sub->id
                    && $d->starts_at <= CarbonImmutable::parse($leave->ends_at)->endOfDay()
                    && $d->ends_at   >= CarbonImmutable::parse($leave->starts_at)->startOfDay();
            });

            $cursor = $iterStart;
            while ($cursor->lessThanOrEqualTo($iterEnd)) {
                $iso = (int) $cursor->isoWeekday();
                if ($iso >= 1 && $iso <= 6) {
                    $dayKey = self::ISO_TO_DAY[$iso];
                    $lessons = $teacherDayLessons?->get($dayKey) ?? collect();

                    if ($lessons->isEmpty()) {
                        // Original teacher had no scheduled lesson that day —
                        // emit a "no-class day" placeholder only if a duty
                        // exists; otherwise nothing to show for this day.
                    } else {
                        foreach ($lessons as $lesson) {
                            $cells[] = [
                                'date'         => $cursor->toDateString(),
                                'day'          => $dayKey,
                                'day_label'    => self::DAY_LABEL[$dayKey],
                                'period'       => $lesson->period,
                                'session'      => $lesson->session,
                                'class_code'   => $lesson->class_code,
                                'subject'      => $lesson->subject,
                                'room'         => $lesson->room,
                                'original_id'  => $original->id,
                                'original'     => $original->name,
                                'substitute_id'=> $sub->id,
                                'substitute'   => $sub->name,
                                'source'       => 'leave',
                                'leave_id'     => $leave->id,
                                'duty_id'      => null,
                                'status'       => $hasDuty ? 'accepted' : 'pending',
                                'gap'          => ! $hasDuty,
                                'gap_reason'   => $hasDuty ? null : 'No duty hand-off recorded',
                            ];
                        }
                    }
                }
                $cursor = $cursor->addDay();
            }
        }

        // Walk standalone DutyAssignments not already covered by a leave row.
        // We treat duties for a substitute teacher whose dates overlap a leave
        // they were assigned to as "already represented" by the leave cells —
        // here we emit duties as standalone rows when no matching approved
        // leave for that teacher exists, OR when the duty is not for a
        // substitute (e.g. supervision, exam invigilation).
        foreach ($duties as $duty) {
            $teacher = $duty->teacher;
            if (! $teacher) {
                continue;
            }
            if ($campus && $teacher->campus !== $campus) {
                continue;
            }
            if ($statusFilter && $duty->status !== $statusFilter) {
                continue;
            }

            // Skip duties already implied by a leave (same substitute, overlapping window).
            $impliedByLeave = $leaves->contains(function (TeacherLeave $l) use ($duty) {
                if ((int) $l->substitute_teacher_id !== (int) $duty->teacher_id) {
                    return false;
                }
                $ls = CarbonImmutable::parse($l->starts_at)->startOfDay();
                $le = CarbonImmutable::parse($l->ends_at)->endOfDay();
                return $duty->starts_at <= $le && $duty->ends_at >= $ls;
            });
            if ($impliedByLeave) {
                continue;
            }

            $dStart = CarbonImmutable::instance($duty->starts_at)->startOfDay();
            $dEnd   = CarbonImmutable::instance($duty->ends_at)->endOfDay();
            $iterStart = $dStart->greaterThan($start) ? $dStart : $start;
            $iterEnd   = $dEnd->lessThan($end) ? $dEnd : $end;

            $weeklyDays = is_array($duty->days_of_week) ? $duty->days_of_week : [];

            $cursor = $iterStart;
            while ($cursor->lessThanOrEqualTo($iterEnd)) {
                $iso = (int) $cursor->isoWeekday();
                $emit = false;
                if ($duty->recurrence === 'weekly') {
                    $emit = in_array($iso, $weeklyDays, true);
                } else {
                    $emit = $iso >= 1 && $iso <= 6;
                }
                if ($emit) {
                    $cells[] = [
                        'date'         => $cursor->toDateString(),
                        'day'          => self::ISO_TO_DAY[$iso] ?? null,
                        'day_label'    => self::ISO_TO_DAY[$iso] ? self::DAY_LABEL[self::ISO_TO_DAY[$iso]] : (string) $iso,
                        'period'       => '—',
                        'session'      => null,
                        'class_code'   => null,
                        'subject'      => $duty->title,
                        'room'         => $duty->location,
                        'original_id'  => null,
                        'original'     => null,
                        'substitute_id'=> $teacher->id,
                        'substitute'   => $teacher->name,
                        'source'       => 'duty',
                        'leave_id'     => null,
                        'duty_id'      => $duty->id,
                        'status'       => $duty->status,
                        'gap'          => false,
                        'gap_reason'   => null,
                    ];
                }
                $cursor = $cursor->addDay();
            }
        }

        // Apply status filter to leave-source rows too.
        if ($statusFilter) {
            $cells = array_values(array_filter(
                $cells,
                fn ($c) => $c['source'] !== 'leave' || $c['status'] === $statusFilter
            ));
        }

        // Sort by date, then session, then period order.
        $periodOrder = array_flip(TeacherSchedule::PERIODS);
        usort($cells, function ($a, $b) use ($periodOrder) {
            return [$a['date'], $a['session'] ?? '', $periodOrder[$a['period']] ?? 99]
                <=> [$b['date'], $b['session'] ?? '', $periodOrder[$b['period']] ?? 99];
        });

        // Build day axis covering the window (ISO 1..6 only).
        $days = [];
        $cursor = $start;
        while ($cursor->lessThanOrEqualTo($end)) {
            $iso = (int) $cursor->isoWeekday();
            if ($iso >= 1 && $iso <= 6) {
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
            $order = array_flip(TeacherSchedule::PERIODS);
            usort($periods, fn ($a, $b) => ($order[$a] ?? 99) <=> ($order[$b] ?? 99));
        }

        $summary = [
            'teachers_on_leave' => count($teachersOnLeave),
            'periods_covered'   => count(array_filter($cells, fn ($c) => ! $c['gap'])),
            'gaps_count'        => count(array_filter($cells, fn ($c) => $c['gap'])),
            'duties_count'      => count(array_filter($cells, fn ($c) => $c['source'] === 'duty')),
        ];

        return [
            'cells'      => $cells,
            'summary'    => $summary,
            'days'       => $days,
            'periods'    => $periods,
            'week_start' => $start->toDateString(),
            'week_end'   => $end->toDateString(),
        ];
    }

    /** Distinct campuses across teachers (for the filter dropdown). */
    public static function availableCampuses(): array
    {
        return Teacher::query()
            ->whereNotNull('campus')
            ->where('campus', '!=', '')
            ->distinct()
            ->orderBy('campus')
            ->pluck('campus')
            ->all();
    }
}
