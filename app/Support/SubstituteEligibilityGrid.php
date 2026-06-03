<?php

namespace App\Support;

use App\Models\DutyAssignment;
use App\Models\Teacher;
use App\Models\TeacherLeave;
use App\Models\TimetableLesson;
use App\Services\Timetable\TeacherSchedule;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

/**
 * Per-period conflict matrix for substitute candidates against the on-leave
 * teacher's actual lesson slots in the leave window.
 *
 * Returns a "demand" list (the slots that need cover) plus per-candidate
 * cell results: free | teaching | duty | off | unavailable.
 */
class SubstituteEligibilityGrid
{
    /**
     * @param  Collection<int, array|Teacher>  $candidates  output of SubstituteSuggester::for()
     *                                                       (annotated rows) OR raw Teacher collection.
     * @return array{
     *   demand: array<int, array{date:string, day:string, day_label:string, period:string, class_code:string, subject:string, lesson_id:int}>,
     *   rows: array<int, array<string, mixed>>,
     *   total_slots: int,
     *   days_count: int
     * }
     */
    public static function buildFor(TeacherLeave $leave, Collection $candidates): array
    {
        $teacher = $leave->teacher;
        if (! $teacher || ! $leave->starts_at || ! $leave->ends_at) {
            return ['demand' => [], 'rows' => [], 'total_slots' => 0, 'days_count' => 0];
        }

        $start = CarbonImmutable::parse($leave->starts_at)->startOfDay();
        $end   = CarbonImmutable::parse($leave->ends_at)->endOfDay();

        $defId = TeacherSchedule::activeDefinitionId();
        if (! $defId || ! $teacher->code) {
            return ['demand' => [], 'rows' => [], 'total_slots' => 0, 'days_count' => 0];
        }

        // ----- Build demand: on-leave teacher's lessons that fall on actual dates in the window -----
        $teacherLessons = TimetableLesson::query()
            ->where('definition_id', $defId)
            ->where('teacher_ref', $teacher->code)
            ->get()
            ->groupBy('day');

        $demand = [];
        $daysWithDemand = [];
        foreach (CarbonPeriod::create($start, $end) as $cursor) {
            $iso = (int) $cursor->isoWeekday();
            if ($iso < 1 || $iso > 6) {
                continue; // skip Sunday
            }
            $dayKey = SubstitutionTimetable::ISO_TO_DAY[$iso] ?? null;
            if (! $dayKey) {
                continue;
            }
            $rows = $teacherLessons->get($dayKey, collect());
            foreach ($rows as $lesson) {
                $demand[] = [
                    'date'       => $cursor->format('Y-m-d'),
                    'date_label' => $cursor->format('d M'),
                    'day'        => $dayKey,
                    'day_label'  => SubstitutionTimetable::DAY_LABEL[$dayKey] ?? $dayKey,
                    'iso'        => $iso,
                    'period'     => $lesson->period,
                    'class_code' => $lesson->class_code,
                    'subject'    => $lesson->subject,
                    'lesson_id'  => $lesson->id,
                ];
                $daysWithDemand[$cursor->format('Y-m-d')] = true;
            }
        }

        // Sort demand: by date ASC, then period order (Roman numerals)
        $periodOrder = array_flip(TeacherSchedule::PERIODS);
        usort($demand, function (array $a, array $b) use ($periodOrder) {
            return [$a['date'], $periodOrder[$a['period']] ?? 99]
                <=> [$b['date'], $periodOrder[$b['period']] ?? 99];
        });

        // ----- Build per-candidate rows -----
        $rows = [];
        foreach ($candidates as $entry) {
            $candidate = $entry instanceof Teacher
                ? $entry
                : ($entry['teacher'] ?? null);
            if (! $candidate instanceof Teacher) {
                continue;
            }
            $isAnnotated = is_array($entry) && isset($entry['is_assignable']);
            $isBlocked   = $isAnnotated ? ! ($entry['is_assignable'] ?? true) : false;
            $blockReason = $isBlocked
                ? ($entry['conflict_reasons'][0] ?? 'Hard conflict')
                : null;
            $tierLabel   = $isAnnotated ? ($entry['tier_label'] ?? '') : '';

            $rows[] = self::buildCandidateRow(
                $candidate,
                $demand,
                $defId,
                $start,
                $end,
                $isBlocked,
                $blockReason,
                $tierLabel,
                $isAnnotated ? ($entry['availability_label'] ?? null) : null,
                $isAnnotated ? ($entry['availability_color'] ?? null) : null,
                $isAnnotated ? ($entry['category_label'] ?? null) : null,
                $isAnnotated ? ($entry['category_color'] ?? null) : null,
                $isAnnotated ? ($entry['category'] ?? 'standard') : 'standard',
            );
        }

        return [
            'demand'      => $demand,
            'rows'        => $rows,
            'total_slots' => count($demand),
            'days_count'  => count($daysWithDemand),
        ];
    }

    protected static function buildCandidateRow(
        Teacher $candidate,
        array $demand,
        int $defId,
        CarbonImmutable $start,
        CarbonImmutable $end,
        bool $isBlocked,
        ?string $blockReason,
        string $tierLabel,
        ?string $availabilityLabel,
        ?string $availabilityColor,
        ?string $categoryLabel,
        ?string $categoryColor,
        string $category,
    ): array {
        // Candidate's own weekly lessons (period × day grid)
        $candLessons = $candidate->code
            ? TimetableLesson::query()
                ->where('definition_id', $defId)
                ->where('teacher_ref', $candidate->code)
                ->get()
            : collect();

        // Index: [day][period] => lesson
        $byDayPeriod = [];
        $daysWithLessons = [];
        foreach ($candLessons as $l) {
            $byDayPeriod[$l->day][$l->period] = $l;
            $daysWithLessons[$l->day] = true;
        }

        // Candidate's active duties overlapping the window
        $duties = DutyAssignment::query()
            ->where('teacher_id', $candidate->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->where(function ($q) use ($end) {
                $q->where('starts_at', '<=', $end);
            })
            ->where(function ($q) use ($start) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $start);
            })
            ->get(['id', 'title', 'starts_at', 'ends_at', 'recurrence', 'days_of_week']);

        $cells = [];
        $free = $teaching = $duty = $off = $unavailable = 0;

        foreach ($demand as $i => $slot) {
            if ($isBlocked) {
                $cells[$i] = [
                    'state'  => 'unavailable',
                    'detail' => $blockReason,
                ];
                $unavailable++;
                continue;
            }

            $iso = $slot['iso'];
            $day = $slot['day'];

            // Lesson conflict?
            $lesson = $byDayPeriod[$day][$slot['period']] ?? null;
            if ($lesson) {
                $cells[$i] = [
                    'state'      => 'teaching',
                    'detail'     => $lesson->class_code . ' · ' . $lesson->subject,
                    'class_code' => $lesson->class_code,
                    'subject'    => $lesson->subject,
                ];
                $teaching++;
                continue;
            }

            // Duty conflict?
            $dutyHit = self::dutyHitsDate($duties, $slot['date'], $iso);
            if ($dutyHit) {
                $cells[$i] = [
                    'state'      => 'duty',
                    'detail'     => $dutyHit->title,
                    'duty_title' => $dutyHit->title,
                    'duty_id'    => $dutyHit->id,
                ];
                $duty++;
                continue;
            }

            // Off-day proxy: candidate has zero lessons on this weekday
            if (empty($daysWithLessons[$day])) {
                $cells[$i] = [
                    'state'  => 'off',
                    'detail' => 'No scheduled work on ' . SubstitutionTimetable::DAY_LABEL[$day],
                ];
                $off++;
                continue;
            }

            $cells[$i] = ['state' => 'free', 'detail' => null];
            $free++;
        }

        $total = count($demand);
        $effective = max(1, $total - $off);
        $freePct = $total === 0 ? 0 : (int) round(($free / max(1, $total)) * 100);

        return [
            'teacher_id'         => $candidate->id,
            'teacher_name'       => $candidate->name,
            'teacher_subject'    => $candidate->subject,
            'teacher_dept'       => $candidate->dept,
            'teacher_campus'     => $candidate->campus,
            'tier_label'         => $tierLabel,
            'category'           => $category,
            'category_label'     => $categoryLabel,
            'category_color'     => $categoryColor,
            'availability_label' => $availabilityLabel,
            'availability_color' => $availabilityColor,
            'is_blocked'         => $isBlocked,
            'block_reason'       => $blockReason,
            'cells'              => $cells,
            'free_count'         => $free,
            'teaching_count'     => $teaching,
            'duty_count'         => $duty,
            'off_count'          => $off,
            'unavailable_count'  => $unavailable,
            'total'              => $total,
            'free_pct'           => $freePct,
            'is_recommended'     => ! $isBlocked && $teaching === 0 && $duty === 0 && $free > 0,
        ];
    }

    /**
     * Returns the first matching duty (or null) for a given date + iso weekday.
     */
    protected static function dutyHitsDate(Collection $duties, string $date, int $iso): ?DutyAssignment
    {
        $d = CarbonImmutable::parse($date);
        foreach ($duties as $duty) {
            $dStart = CarbonImmutable::parse($duty->starts_at)->startOfDay();
            $dEnd   = $duty->ends_at
                ? CarbonImmutable::parse($duty->ends_at)->endOfDay()
                : $dStart->endOfDay();

            if ($d->lt($dStart) || $d->gt($dEnd)) {
                continue;
            }

            if ($duty->recurrence === 'weekly') {
                $days = (array) ($duty->days_of_week ?? []);
                if ($days && ! in_array($iso, $days, true)) {
                    continue;
                }
                return $duty;
            }
            // 'once' or anything else: any date in the window matches
            return $duty;
        }
        return null;
    }
}
