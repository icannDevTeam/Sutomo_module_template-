<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherTraining;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TeacherProfileSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = Teacher::limit(20)->get();
        if ($teachers->isEmpty()) {
            $this->command?->warn('No teachers — skipping TeacherProfileSeeder.');
            return;
        }

        $awardsPool = [
            'Best Homeroom Teacher 2024',
            'Outstanding Mentor 2023',
            'Innovative Educator Award 2025',
            'Excellence in Pedagogy 2024',
            '15-Year Service Honoree',
            'Yayasan Recognition Letter 2025',
        ];
        $initiativesPool = [
            'Founded Robotics Club',
            'Launched Saturday Tutoring',
            'Curriculum Mapping Lead',
            'Parent Newsletter Editor',
            'Wellness Week Coordinator',
            'PD Workshop Facilitator',
        ];
        $trainingsPool = [
            ['title' => 'IB Category 2 — Mathematics AA', 'provider' => 'IBO', 'category' => 'subject',    'hours' => 16],
            ['title' => 'Differentiated Instruction',     'provider' => 'Sekolah Cikal', 'category' => 'pedagogy', 'hours' => 8],
            ['title' => 'Google Workspace for Educators', 'provider' => 'Google EDU',    'category' => 'tech',     'hours' => 12],
            ['title' => 'Restorative Practices Level 1',  'provider' => 'IIRP',          'category' => 'wellness', 'hours' => 14],
            ['title' => 'Middle Leaders Programme',       'provider' => 'NIE Singapore', 'category' => 'leadership','hours' => 24],
            ['title' => 'Assessment for Learning',        'provider' => 'British Council','category' => 'pedagogy','hours' => 10],
            ['title' => 'STEM Project-Based Learning',    'provider' => 'Buck Institute','category' => 'pedagogy', 'hours' => 18],
            ['title' => 'Child Protection Refresher',     'provider' => 'Internal',      'category' => 'wellness', 'hours' => 4],
        ];

        foreach ($teachers as $i => $t) {
            $awardCount = ($i % 3 === 0) ? 2 : ($i % 2 === 0 ? 1 : 0);
            $initCount  = ($i % 4 === 0) ? 2 : 1;

            $t->update([
                'awards'         => $awardCount ? collect($awardsPool)->shuffle()->take($awardCount)->values()->all() : null,
                'initiatives'    => collect($initiativesPool)->shuffle()->take($initCount)->values()->all(),
                'children_quota' => ($i % 4 === 0) ? 2 : (($i % 3 === 0) ? 1 : null),
            ]);

            $sessionsCount = 3 + ($i % 4);
            for ($k = 0; $k < $sessionsCount; $k++) {
                $tr = collect($trainingsPool)->shuffle()->first();
                $start = Carbon::now()->subMonths(rand(1, 18))->startOfDay();
                TeacherTraining::create([
                    'teacher_id'  => $t->id,
                    'title'       => $tr['title'],
                    'provider'    => $tr['provider'],
                    'category'    => $tr['category'],
                    'hours'       => $tr['hours'],
                    'starts_on'   => $start,
                    'ends_on'     => $start->copy()->addDays(rand(1, 4)),
                    'status'      => $k === 0 && $i % 5 === 0 ? 'planned' : 'completed',
                    'notes'       => null,
                ]);
            }
        }

        // Link a few students to teachers as parent (children quota)
        $teachersWithQuota = Teacher::whereNotNull('children_quota')->get();
        $studentPool = Student::limit(40)->get();
        $sIndex = 0;
        foreach ($teachersWithQuota as $t) {
            $needed = (int) $t->children_quota;
            for ($k = 0; $k < $needed && $sIndex < $studentPool->count(); $k++, $sIndex++) {
                $studentPool[$sIndex]->update(['parent_teacher_id' => $t->id]);
            }
        }
    }
}
