<?php

namespace Database\Seeders;

use App\Models\DutyAssignment;
use App\Models\Teacher;
use App\Models\TeacherLeave;
use App\Models\TimetableDefinition;
use App\Models\TimetableLesson;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

/**
 * Demo data for the /principal/substitution-timetable page.
 *
 * Builds:
 *  - a published TimetableDefinition + lessons keyed to REAL Teacher.code values
 *    (Mon..Sat × periods I..V), so the join can produce coverage cells.
 *  - 3 approved TeacherLeaves overlapping the current week with substitutes
 *    (one of them deliberately has no DutyAssignment → renders as GAP rows).
 *  - 2 standalone DutyAssignments (one once-off, one weekly recurring).
 */
class SubstitutionTimetableDemoSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = Teacher::query()
            ->whereNotNull('code')
            ->where('code', '!=', '')
            ->orderBy('id')
            ->take(8)
            ->get();

        if ($teachers->count() < 5) {
            $this->command?->warn('Not enough teachers with codes — seed teachers first.');
            return;
        }

        // Pick stable roles.
        [$tA, $tB, $tC, $sA, $sB] = [
            $teachers[0],
            $teachers[1],
            $teachers[2],
            $teachers[3],
            $teachers[4],
        ];
        $tD = $teachers[5] ?? $teachers[0]; // gap-leave teacher
        $sC = $teachers[6] ?? $teachers[3]; // standalone duty teacher
        $sD = $teachers[7] ?? $teachers[4]; // weekly recurring duty teacher

        $def = TimetableDefinition::firstOrCreate(
            ['term' => 'TP 2025/2026', 'school_unit' => 'ALL'],
            ['status' => 'published']
        );
        if ($def->status !== 'published') {
            $def->update(['status' => 'published']);
        }

        // Wipe demo lessons that target our chosen teachers, then reseed.
        TimetableLesson::where('definition_id', $def->id)
            ->whereIn('teacher_ref', [$tA->code, $tB->code, $tC->code, $tD->code])
            ->delete();

        $days    = ['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT'];
        $periods = ['I', 'II', 'III', 'IV', 'V'];

        // Teacher A — Math, class 7-A
        $this->makeRow($def->id, $tA->code, '7-A', 'MATEMATIKA', 'pagi',  ['SENIN'=>'I','SELASA'=>'II','RABU'=>'I','KAMIS'=>'III','JUMAT'=>'II']);
        // Teacher B — English, class 8-B
        $this->makeRow($def->id, $tB->code, '8-B', 'B. INGGRIS', 'pagi',  ['SENIN'=>'III','SELASA'=>'IV','RABU'=>'II','KAMIS'=>'I','JUMAT'=>'V']);
        // Teacher C — Biology, class 9-A
        $this->makeRow($def->id, $tC->code, '9-A', 'BIOLOGI',     'pagi', ['SENIN'=>'V','SELASA'=>'III','RABU'=>'IV','KAMIS'=>'V','JUMAT'=>'I']);
        // Teacher D — History, class 7-B (will go on leave WITHOUT a duty)
        $this->makeRow($def->id, $tD->code, '7-B', 'SEJARAH',     'pagi', ['SENIN'=>'II','RABU'=>'III','KAMIS'=>'IV']);

        // ----- Leaves overlapping current week -----
        $weekStart = CarbonImmutable::today()->startOfWeek();

        // Wipe prior demo leaves (same teachers + dates inside this week).
        TeacherLeave::query()
            ->whereIn('teacher_id', [$tA->id, $tB->id, $tD->id])
            ->where('starts_at', '>=', $weekStart->subWeek()->toDateString())
            ->where('starts_at', '<=', $weekStart->addWeek()->toDateString())
            ->delete();

        // Leave 1: Teacher A — full week, substitute = sA, WITH duty.
        $leaveA = TeacherLeave::create([
            'teacher_id'            => $tA->id,
            'type'                  => 'sick',
            'reason'                => 'Demo: full-week sick leave (covered).',
            'starts_at'             => $weekStart->toDateString(),
            'ends_at'               => $weekStart->addDays(4)->toDateString(),
            'status'                => 'approved',
            'substitute_teacher_id' => $sA->id,
            'auto_search_enabled'   => false,
            'decided_at'            => now(),
        ]);

        // Leave 2: Teacher B — first 3 days of week, sub = sB, WITH duty.
        $leaveB = TeacherLeave::create([
            'teacher_id'            => $tB->id,
            'type'                  => 'prior',
            'reason'                => 'Demo: short prior-notice leave (covered).',
            'starts_at'             => $weekStart->toDateString(),
            'ends_at'               => $weekStart->addDays(2)->toDateString(),
            'status'                => 'approved',
            'substitute_teacher_id' => $sB->id,
            'auto_search_enabled'   => false,
            'decided_at'            => now(),
        ]);

        // Leave 3: Teacher D — Tuesday + Wednesday, sub assigned, NO duty (GAP).
        TeacherLeave::create([
            'teacher_id'            => $tD->id,
            'type'                  => 'emergency',
            'reason'                => 'Demo: emergency leave with no duty hand-off (renders as GAP).',
            'starts_at'             => $weekStart->addDays(1)->toDateString(),
            'ends_at'               => $weekStart->addDays(2)->toDateString(),
            'status'                => 'approved',
            'substitute_teacher_id' => $sC->id,
            'auto_search_enabled'   => false,
            'decided_at'            => now(),
        ]);

        // ----- Duty assignments -----
        // Wipe prior demo duties (those substitutes within the current week window).
        DutyAssignment::query()
            ->whereIn('teacher_id', [$sA->id, $sB->id, $sD->id])
            ->where('starts_at', '>=', $weekStart->subWeek())
            ->where('starts_at', '<=', $weekStart->addWeeks(2))
            ->delete();

        DutyAssignment::create([
            'teacher_id'    => $sA->id,
            'title'         => 'Cover for ' . $tA->name,
            'location'      => 'Class 7-A',
            'starts_at'     => $weekStart->setTime(7, 0),
            'ends_at'       => $weekStart->addDays(4)->setTime(15, 0),
            'recurrence'    => 'once',
            'status'        => 'accepted',
            'assigned_by'   => 'Principal Hartono',
            'academic_year' => DutyAssignment::academicYearFor($weekStart),
        ]);

        DutyAssignment::create([
            'teacher_id'    => $sB->id,
            'title'         => 'Cover for ' . $tB->name,
            'location'      => 'Class 8-B',
            'starts_at'     => $weekStart->setTime(7, 0),
            'ends_at'       => $weekStart->addDays(2)->setTime(15, 0),
            'recurrence'    => 'once',
            'status'        => 'pending',
            'assigned_by'   => 'Principal Hartono',
            'academic_year' => DutyAssignment::academicYearFor($weekStart),
        ]);

        // Standalone (non-leave) duty: morning-gate supervision, every Tue + Thu.
        DutyAssignment::create([
            'teacher_id'    => $sD->id,
            'title'         => 'Morning gate supervision',
            'location'      => 'Main entrance',
            'starts_at'     => $weekStart->setTime(6, 30),
            'ends_at'       => $weekStart->addWeeks(2)->setTime(7, 15),
            'recurrence'    => 'weekly',
            'days_of_week'  => [2, 4],
            'status'        => 'accepted',
            'assigned_by'   => 'Principal Hartono',
            'academic_year' => DutyAssignment::academicYearFor($weekStart),
        ]);

        $this->command?->info('Substitution timetable demo seeded for week starting ' . $weekStart->toDateString());
    }

    /**
     * @param  array<string, string>  $dayPeriod  e.g. ['SENIN' => 'I', 'RABU' => 'III']
     */
    protected function makeRow(int $defId, string $teacherRef, string $class, string $subject, string $session, array $dayPeriod): void
    {
        foreach ($dayPeriod as $day => $period) {
            TimetableLesson::create([
                'definition_id' => $defId,
                'teacher_ref'   => $teacherRef,
                'class_code'    => $class,
                'subject'       => $subject,
                'day'           => $day,
                'period'        => $period,
                'session'       => $session,
                'room'          => $class,
            ]);
        }
    }
}
