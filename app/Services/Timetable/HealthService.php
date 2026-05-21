<?php

namespace App\Services\Timetable;

use App\Models\SubjectRequirement;
use App\Models\TeacherLoad;
use App\Models\TimetableLesson;
use Illuminate\Support\Collection;

/**
 * Aggregates timetable health into actionable, jump-to-fix issues.
 *
 * Each returned issue:
 *   [
 *     'category' => 'subject_deficit' | 'subject_excess' | 'over_cap' | 'under_cap'
 *                 | 'cell_conflict' | 'unfilled_required'
 *     'severity' => 'error' | 'warning' | 'info',
 *     'title'    => string,
 *     'detail'   => string,
 *     'fix'      => ['teacher' => ?string, 'session' => ?string, 'period' => ?string, 'day' => ?string],
 *   ]
 */
class HealthService
{
    /** @return array{summary: array, issues: array, byCategory: array} */
    public function report(int $definitionId): array
    {
        $lessons = TimetableLesson::where('definition_id', $definitionId)->get();
        $loads   = TeacherLoad::where('definition_id', $definitionId)->get()->keyBy('teacher_ref');
        $reqs    = SubjectRequirement::where('definition_id', $definitionId)->get();

        $issues = collect();

        // 1) Cell-level conflicts surfaced from cached flags
        foreach ($lessons->whereNotNull('conflicts') as $l) {
            foreach ($l->conflicts as $c) {
                $issues->push([
                    'category' => 'cell_conflict',
                    'severity' => $c['severity'] ?? 'warning',
                    'title'    => $c['message'] ?? ($c['code'] ?? 'Conflict'),
                    'detail'   => "{$l->teacher_ref} · {$l->subject} · {$l->class_code} · {$l->day} {$l->period} ({$l->session})",
                    'fix'      => [
                        'teacher' => $l->teacher_ref,
                        'session' => $l->session,
                        'period'  => $l->period,
                        'day'     => $l->day,
                    ],
                ]);
            }
        }

        // 2) Subject requirement deficits / excesses per class
        $classes = $lessons->pluck('class_code')->unique();
        foreach ($classes as $classCode) {
            $grade = $this->extractGrade($classCode);
            if ($grade === null) continue;
            $classLessons = $lessons->where('class_code', $classCode);
            $reqsForGrade = $reqs->where('grade_level', $grade);
            foreach ($reqsForGrade as $req) {
                $have = $classLessons->where('subject', $req->subject)->count();
                $need = (int) $req->weekly_hours;
                if ($have < $need) {
                    $diff = $need - $have;
                    $issues->push([
                        'category' => 'subject_deficit',
                        'severity' => 'error',
                        'title'    => "{$classCode} needs {$diff} more jam {$req->subject}",
                        'detail'   => "Currently {$have} / {$need} jam per week",
                        'fix'      => ['teacher' => null, 'session' => null, 'period' => null, 'day' => null],
                    ]);
                } elseif ($have > $need) {
                    $diff = $have - $need;
                    $issues->push([
                        'category' => 'subject_excess',
                        'severity' => 'warning',
                        'title'    => "{$classCode} has {$diff} extra jam {$req->subject}",
                        'detail'   => "Currently {$have} / {$need} jam per week",
                        'fix'      => ['teacher' => null, 'session' => null, 'period' => null, 'day' => null],
                    ]);
                }
            }
        }

        // 3) Teacher cap status
        $byTeacher = $lessons->groupBy('teacher_ref');
        foreach ($loads as $load) {
            $total = ($byTeacher->get($load->teacher_ref, collect()))->count();
            if ($load->weekly_cap && $total > $load->weekly_cap) {
                $issues->push([
                    'category' => 'over_cap',
                    'severity' => 'warning',
                    'title'    => "Teacher {$load->teacher_ref} over cap ({$total} / {$load->weekly_cap})",
                    'detail'   => 'Reassign or reduce lessons',
                    'fix'      => ['teacher' => $load->teacher_ref, 'session' => null, 'period' => null, 'day' => null],
                ]);
            } elseif ($load->weekly_cap && $total < $load->weekly_cap * 0.5) {
                $issues->push([
                    'category' => 'under_cap',
                    'severity' => 'info',
                    'title'    => "Teacher {$load->teacher_ref} is under-loaded ({$total} / {$load->weekly_cap})",
                    'detail'   => 'Could take more lessons',
                    'fix'      => ['teacher' => $load->teacher_ref, 'session' => null, 'period' => null, 'day' => null],
                ]);
            }
        }

        $summary = [
            'total'   => $issues->count(),
            'errors'  => $issues->where('severity', 'error')->count(),
            'warns'   => $issues->where('severity', 'warning')->count(),
            'info'    => $issues->where('severity', 'info')->count(),
            'lessons' => $lessons->count(),
        ];

        return [
            'summary'    => $summary,
            'issues'     => $issues->values()->all(),
            'byCategory' => $issues->groupBy('category')->map->count()->all(),
        ];
    }

    private function extractGrade(string $classCode): ?string
    {
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
