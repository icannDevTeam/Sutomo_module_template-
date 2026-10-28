<?php

namespace Database\Seeders;

use App\Models\DutyAssignment;
use App\Models\LeaveType;
use App\Models\Teacher;
use App\Models\TeacherLeave;
use App\Models\TimetableDefinition;
use App\Models\TimetableLesson;
use App\Services\Timetable\TeacherSchedule;
use App\Support\SubstitutionTimetable;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class IsolatedSubstitutionDemoSeeder extends Seeder
{
    public function run(): void
    {
        $definitionId = TeacherSchedule::activeDefinitionId();
        $definition = $definitionId ? TimetableDefinition::find($definitionId) : null;
        if (! $definition || $definition->status !== 'published') {
            throw new RuntimeException('An active published timetable is required. No demo data was created.');
        }
        if (LeaveType::where('key', 'sick')->where('requires_substitute', false)->exists()) {
            throw new RuntimeException('Sick leave is configured not to require a substitute. No demo data was created.');
        }

        $week = CarbonImmutable::today()->startOfWeek();
        DB::transaction(function () use ($definitionId, $week): void {
            foreach ([
                'confirmed' => 'accepted',
                'awaiting' => 'pending',
                'no_handoff' => null,
                'declined' => 'declined',
                'uncovered' => null,
                'pending' => null,
            ] as $state => $dutyStatus) {
                $teacher = $this->teacher($state);
                $substitute = in_array($state, ['uncovered', 'pending'], true)
                    ? null : $this->teacher("sub-{$state}");

                foreach (array_keys(SubstitutionTimetable::DAY_TO_ISO) as $day) {
                    TimetableLesson::firstOrCreate([
                        'definition_id' => $definitionId,
                        'teacher_ref' => $teacher->code,
                        'day' => $day,
                        'period' => 'I',
                        'session' => 'pagi',
                        'class_code' => $teacher->code,
                    ], ['subject' => 'Demo substitution lesson', 'room' => 'Demo room']);
                }

                $reason = 'Isolated substitution demo: ' . $state . ' / ' . $week->toDateString();
                if (TeacherLeave::where('teacher_id', $teacher->id)->where('reason', $reason)->exists()) {
                    $this->command?->info("Skipping {$state}: this week's demo already exists.");
                    continue;
                }

                TeacherLeave::create([
                    'teacher_id' => $teacher->id,
                    'substitute_teacher_id' => $substitute?->id,
                    'type' => 'sick',
                    'reason' => $reason,
                    'starts_at' => $week,
                    'ends_at' => $week->addDays(5),
                    'status' => $state === 'pending' ? 'pending' : 'approved',
                    'auto_search_enabled' => false,
                    'decided_at' => $state === 'pending' ? null : now(),
                ]);

                if ($dutyStatus !== null) {
                    DutyAssignment::create([
                        'teacher_id' => $substitute->id,
                        'title' => 'Demo cover: ' . $state,
                        'location' => 'Demo room',
                        'starts_at' => $week->setTime(7, 0),
                        'ends_at' => $week->addDays(5)->setTime(15, 0),
                        'recurrence' => 'once',
                        'status' => $dutyStatus,
                        'assigned_by' => 'Isolated substitution demo',
                        'academic_year' => DutyAssignment::academicYearFor($week),
                    ]);
                }
            }

            $dutyTeacher = $this->teacher('weekly-duty');
            DutyAssignment::firstOrCreate([
                'teacher_id' => $dutyTeacher->id,
                'title' => 'Demo weekly supervision / ' . $week->toDateString(),
            ], [
                'location' => 'Demo gate',
                'starts_at' => $week->setTime(7, 0),
                'ends_at' => $week->addDays(5)->setTime(8, 0),
                'recurrence' => 'weekly',
                'days_of_week' => [1, 3, 5],
                'status' => 'accepted',
                'assigned_by' => 'Isolated substitution demo',
                'academic_year' => DutyAssignment::academicYearFor($week),
            ]);
        });

        $this->command?->info('Isolated substitution demo ready for week of ' . $week->toDateString() . '. Filter SMA or search "Substitution Demo". Existing data was preserved.');
    }

    private function teacher(string $scenario): Teacher
    {
        $code = 'SUB-DEMO-' . strtoupper(str_replace('_', '-', $scenario));
        $name = 'Substitution Demo - ' . $scenario;
        $teacher = Teacher::firstOrCreate(
            ['code' => $code],
            ['name' => $name, 'campus' => 'sma', 'status' => 'pkwt_1']
        );
        if ($teacher->name !== $name) {
            throw new RuntimeException("Teacher code {$code} already belongs to a different teacher. Demo seeding was rolled back.");
        }

        return $teacher;
    }
}
