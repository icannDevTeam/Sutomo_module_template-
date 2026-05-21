<?php

namespace Database\Seeders;

use App\Models\SubjectRequirement;
use App\Models\TeacherLoad;
use App\Models\TimetableDefinition;
use App\Models\TimetableLesson;
use App\Services\Timetable\ConflictService;
use Illuminate\Database\Seeder;

class TimetableSeeder extends Seeder
{
    public function run(): void
    {
        $def = TimetableDefinition::firstOrCreate(
            ['term' => 'TP 2025/2026', 'school_unit' => 'ALL'],
            ['status' => 'draft']
        );

        // Wipe any prior demo state for clean reseed.
        TimetableLesson::where('definition_id', $def->id)->delete();
        TeacherLoad::where('definition_id', $def->id)->delete();
        SubjectRequirement::where('definition_id', $def->id)->delete();

        // ---- Teacher loads (caps + subjects + unavailable slots) ----
        $loads = [
            'T-001' => [
                'weekly_cap'        => 28,
                'subjects'          => ['BIOLOGI', 'LAB IPA', 'IPA'],
                'unavailable_slots' => [],
            ],
            'T-002' => [
                'weekly_cap'        => 24,
                'subjects'          => ['MATEMATIKA'],
                'unavailable_slots' => [
                    ['day' => 'JUMAT', 'period' => 'I', 'session' => 'pagi'],
                ],
            ],
            'T-003' => [
                'weekly_cap'        => 24,
                'subjects'          => ['B. INGGRIS', 'BAHASA INGGRIS'],
                'unavailable_slots' => [],
            ],
        ];
        foreach ($loads as $ref => $cfg) {
            TeacherLoad::create([
                'definition_id'     => $def->id,
                'teacher_ref'       => $ref,
                'weekly_cap'        => $cfg['weekly_cap'],
                'subjects'          => $cfg['subjects'],
                'unavailable_slots' => $cfg['unavailable_slots'],
            ]);
        }

        // ---- Subject hour requirements per grade ----
        $reqs = [
            ['grade_level' => '7', 'subject' => 'BIOLOGI',    'weekly_hours' => 3],
            ['grade_level' => '8', 'subject' => 'BIOLOGI',    'weekly_hours' => 3],
            ['grade_level' => '9', 'subject' => 'BIOLOGI',    'weekly_hours' => 3],
            ['grade_level' => '7', 'subject' => 'B. INGGRIS', 'weekly_hours' => 4],
            ['grade_level' => '8', 'subject' => 'B. INGGRIS', 'weekly_hours' => 4],
            ['grade_level' => '9', 'subject' => 'B. INGGRIS', 'weekly_hours' => 4],
            ['grade_level' => '4', 'subject' => 'MATEMATIKA', 'weekly_hours' => 5],
            ['grade_level' => '5', 'subject' => 'MATEMATIKA', 'weekly_hours' => 5],
            ['grade_level' => '6', 'subject' => 'MATEMATIKA', 'weekly_hours' => 5],
        ];
        foreach ($reqs as $r) {
            SubjectRequirement::create(array_merge($r, ['definition_id' => $def->id]));
        }

        // ---- Demo lessons (mirrors prior in-memory grids) ----
        $grids = [
            'T-001' => [
                'pagi' => [
                    'II-SENIN'   => ['BIOLOGI', 'P VII-02'],
                    'II-RABU'    => ['BIOLOGI', 'P VII-02'],
                    'II-KAMIS'   => ['BIOLOGI', 'P VII-01'],
                    'II-JUMAT'   => ['BIOLOGI', 'P VII-04'],
                    'III-SENIN'  => ['BIOLOGI', 'P VIII-03'],
                    'III-RABU'   => ['BIOLOGI', 'P IX-02'],
                    'III-KAMIS'  => ['BIOLOGI', 'P VII-01'],
                    'III-JUMAT'  => ['BIOLOGI', 'P VII-04'],
                    'IV-SENIN'   => ['BIOLOGI', 'P VIII-03'],
                    'IV-RABU'    => ['BIOLOGI', 'P VIII-04'],
                    'V-SELASA'   => ['BIOLOGI', 'P VII-03'],
                    'V-RABU'     => ['BIOLOGI', 'P VII-04'],
                    'V-KAMIS'    => ['BIOLOGI', 'P IX-02'],
                    'VI-SENIN'   => ['BIOLOGI', 'P VIII-02'],
                    'VI-SELASA'  => ['BIOLOGI', 'P VII-03'],
                    'VI-RABU'    => ['BIOLOGI', 'P VII-01'],
                    'VII-SENIN'  => ['BIOLOGI', 'P VIII-02'],
                    'VII-RABU'   => ['BIOLOGI', 'P VIII-01'],
                ],
                'sore' => [
                    'II-SELASA'  => ['BIOLOGI', 'P IX-01'],
                    'II-JUMAT'   => ['LAB IPA', 'P VII-04'],
                    'III-SELASA' => ['BIOLOGI', 'P IX-01'],
                    'III-JUMAT'  => ['LAB IPA', 'P VII-04'],
                ],
            ],
            'T-002' => [
                'pagi' => [
                    'I-SENIN'   => ['MATEMATIKA', '4-A'],
                    'I-SELASA'  => ['MATEMATIKA', '4-B'],
                    'II-SENIN'  => ['MATEMATIKA', '4-A'],
                    'II-RABU'   => ['MATEMATIKA', '5-A'],
                    'IV-KAMIS'  => ['MATEMATIKA', '5-B'],
                    'V-RABU'    => ['MATEMATIKA', '6-A'],
                ],
                'sore' => [],
            ],
            'T-003' => [
                'pagi' => [
                    'I-SENIN' => ['B. INGGRIS', 'P VII-01'],
                    'IV-RABU' => ['B. INGGRIS', 'P IX-01'],
                ],
                'sore' => [],
            ],
        ];

        $created = [];
        foreach ($grids as $teacherRef => $sessions) {
            foreach ($sessions as $session => $cells) {
                foreach ($cells as $key => [$subject, $class]) {
                    [$period, $day] = explode('-', $key, 2);
                    $created[] = TimetableLesson::create([
                        'definition_id' => $def->id,
                        'teacher_ref'   => $teacherRef,
                        'class_code'    => $class,
                        'subject'       => $subject,
                        'day'           => $day,
                        'period'        => $period,
                        'session'       => $session,
                    ]);
                }
            }
        }

        // Recompute conflict flags now that everything is in.
        $svc = app(ConflictService::class);
        foreach ($created as $lesson) {
            $svc->checkAndStore($lesson);
        }
    }
}
