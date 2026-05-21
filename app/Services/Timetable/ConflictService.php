<?php

namespace App\Services\Timetable;

use App\Models\SubjectRequirement;
use App\Models\TeacherLoad;
use App\Models\TimetableLesson;
use Illuminate\Support\Collection;

/**
 * Detects scheduling conflicts for a single lesson against the full timetable.
 *
 * Returns an array of conflict objects:
 *   ['code' => 'teacher_double_booked', 'message' => '...', 'severity' => 'error']
 *
 * Severity: 'error' blocks publish, 'warning' is informational.
 */
class ConflictService
{
    /**
     * Check one lesson against everything else in the same definition.
     *
     * @return array<int, array{code: string, message: string, severity: string}>
     */
    public function check(TimetableLesson $lesson): array
    {
        $issues = [];

        // 1. Teacher double-booked: same teacher, same slot, different lesson.
        $teacherClash = TimetableLesson::query()
            ->where('definition_id', $lesson->definition_id)
            ->where('teacher_ref', $lesson->teacher_ref)
            ->where('day', $lesson->day)
            ->where('period', $lesson->period)
            ->where('session', $lesson->session)
            ->when($lesson->exists, fn ($q) => $q->where('id', '!=', $lesson->id))
            ->first();
        if ($teacherClash) {
            $issues[] = [
                'code'     => 'teacher_double_booked',
                'severity' => 'error',
                'message'  => "Teacher already in {$teacherClash->class_code} this slot",
            ];
        }

        // 2. Class double-booked: same class, same slot, different teacher/lesson.
        $classClash = TimetableLesson::query()
            ->where('definition_id', $lesson->definition_id)
            ->where('class_code', $lesson->class_code)
            ->where('day', $lesson->day)
            ->where('period', $lesson->period)
            ->where('session', $lesson->session)
            ->when($lesson->exists, fn ($q) => $q->where('id', '!=', $lesson->id))
            ->first();
        if ($classClash) {
            $issues[] = [
                'code'     => 'class_double_booked',
                'severity' => 'error',
                'message'  => "{$lesson->class_code} already has {$classClash->subject} this slot",
            ];
        }

        // 3. Room clash: same room, same slot, different lesson.
        if ($lesson->room) {
            $roomClash = TimetableLesson::query()
                ->where('definition_id', $lesson->definition_id)
                ->where('room', $lesson->room)
                ->where('day', $lesson->day)
                ->where('period', $lesson->period)
                ->where('session', $lesson->session)
                ->when($lesson->exists, fn ($q) => $q->where('id', '!=', $lesson->id))
                ->first();
            if ($roomClash) {
                $issues[] = [
                    'code'     => 'room_clash',
                    'severity' => 'error',
                    'message'  => "Room {$lesson->room} already used by {$roomClash->class_code}",
                ];
            }
        }

        // 4. Teacher over weekly cap (only counted after this lesson saved).
        $load = TeacherLoad::query()
            ->where('definition_id', $lesson->definition_id)
            ->where('teacher_ref', $lesson->teacher_ref)
            ->first();
        if ($load && $load->weekly_cap) {
            $total = TimetableLesson::query()
                ->where('definition_id', $lesson->definition_id)
                ->where('teacher_ref', $lesson->teacher_ref)
                ->when($lesson->exists, fn ($q) => $q->where('id', '!=', $lesson->id))
                ->count() + 1;
            if ($total > $load->weekly_cap) {
                $issues[] = [
                    'code'     => 'teacher_over_cap',
                    'severity' => 'warning',
                    'message'  => "Teacher load {$total} jam exceeds cap {$load->weekly_cap}",
                ];
            }
            if (! empty($load->subjects) && ! in_array($lesson->subject, $load->subjects, true)) {
                $issues[] = [
                    'code'     => 'subject_not_in_load',
                    'severity' => 'warning',
                    'message'  => "{$lesson->subject} is not in teacher's assigned subjects",
                ];
            }
            foreach (($load->unavailable_slots ?? []) as $slot) {
                if (
                    ($slot['day']     ?? null) === $lesson->day &&
                    ($slot['period']  ?? null) === $lesson->period &&
                    ($slot['session'] ?? null) === $lesson->session
                ) {
                    $issues[] = [
                        'code'     => 'teacher_unavailable',
                        'severity' => 'error',
                        'message'  => 'Teacher is marked unavailable this slot',
                    ];
                    break;
                }
            }
        }

        // 5. Subject quota exceeded for this class.
        $grade = $this->extractGrade($lesson->class_code);
        if ($grade !== null) {
            $req = SubjectRequirement::query()
                ->where('definition_id', $lesson->definition_id)
                ->where('grade_level', $grade)
                ->where('subject', $lesson->subject)
                ->first();
            if ($req && $req->weekly_hours) {
                $count = TimetableLesson::query()
                    ->where('definition_id', $lesson->definition_id)
                    ->where('class_code', $lesson->class_code)
                    ->where('subject', $lesson->subject)
                    ->when($lesson->exists, fn ($q) => $q->where('id', '!=', $lesson->id))
                    ->count() + 1;
                if ($count > $req->weekly_hours) {
                    $issues[] = [
                        'code'     => 'subject_quota_exceeded',
                        'severity' => 'warning',
                        'message'  => "{$lesson->subject} for {$lesson->class_code} has {$count}/{$req->weekly_hours} jam",
                    ];
                }
            }
        }

        return $issues;
    }

    /**
     * Persist conflict flags on the lesson.
     */
    public function checkAndStore(TimetableLesson $lesson): array
    {
        $issues = $this->check($lesson);
        $lesson->conflicts = $issues ?: null;
        $lesson->saveQuietly();
        return $issues;
    }

    /**
     * Recompute conflicts for every lesson in the same slot as $lesson
     * (since adding/changing one may resolve or create issues for others).
     */
    public function rescanSlotNeighbors(TimetableLesson $lesson): void
    {
        $neighbors = TimetableLesson::query()
            ->where('definition_id', $lesson->definition_id)
            ->where('day', $lesson->day)
            ->where('period', $lesson->period)
            ->where('session', $lesson->session)
            ->where(function ($q) use ($lesson) {
                $q->where('teacher_ref', $lesson->teacher_ref)
                  ->orWhere('class_code', $lesson->class_code)
                  ->when($lesson->room, fn ($q2) => $q2->orWhere('room', $lesson->room));
            })
            ->when($lesson->exists, fn ($q) => $q->where('id', '!=', $lesson->id))
            ->get();

        foreach ($neighbors as $n) {
            $issues = $this->check($n);
            $n->conflicts = $issues ?: null;
            $n->saveQuietly();
        }
    }

    /**
     * Summarise issues for one teacher's whole sheet.
     */
    public function teacherSheetSummary(int $definitionId, string $teacherRef): Collection
    {
        return TimetableLesson::query()
            ->where('definition_id', $definitionId)
            ->where('teacher_ref', $teacherRef)
            ->whereNotNull('conflicts')
            ->get();
    }

    private function extractGrade(string $classCode): ?string
    {
        // "P VII-01" -> "7" ; "4-A" -> "4"
        if (preg_match('/\b(VII|VIII|IX|X|XI|XII|IV|V|VI|I{1,3})\b/u', $classCode, $m)) {
            return (string) $this->romanToInt($m[1]);
        }
        if (preg_match('/^(\d{1,2})\b/', $classCode, $m)) {
            return $m[1];
        }
        return null;
    }

    private function romanToInt(string $r): int
    {
        $map = ['I' => 1, 'V' => 5, 'X' => 10, 'L' => 50, 'C' => 100];
        $result = 0; $prev = 0;
        for ($i = strlen($r) - 1; $i >= 0; $i--) {
            $v = $map[$r[$i]] ?? 0;
            $result += $v < $prev ? -$v : $v;
            $prev = $v;
        }
        return $result;
    }
}
