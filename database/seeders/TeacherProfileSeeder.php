<?php

namespace Database\Seeders;

use App\Models\ParentCommunication;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\TeacherCertification;
use App\Models\TeacherClearance;
use App\Models\TeacherEmploymentEvent;
use App\Models\TeacherGoal;
use App\Models\TeacherJournalEntry;
use App\Models\TeacherObservation;
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

        $this->seedPhase3to6($teachers);
    }

    protected function seedPhase3to6($teachers): void
    {
        $certPool = [
            ['name' => 'Sertifikat Pendidik (Sergub)', 'issuer' => 'Kemendikbud RI', 'gov' => true, 'country' => 'ID', 'hours' => 120],
            ['name' => 'NPK Yayasan',                  'issuer' => 'Yayasan',         'gov' => true, 'country' => 'ID', 'hours' => 40],
            ['name' => 'Cambridge CELTA',              'issuer' => 'Cambridge Assessment', 'gov' => false, 'country' => 'UK', 'hours' => 120],
            ['name' => 'IB MYP Certificate',           'issuer' => 'IBO',             'gov' => false, 'country' => 'CH', 'hours' => 60],
            ['name' => 'Google Certified Educator L1', 'issuer' => 'Google',          'gov' => false, 'country' => 'US', 'hours' => 12],
        ];
        $clearanceTypes = ['criminal_record','child_protection','medical','vaccination'];
        $employmentTitles = ['Wali Kelas','Koordinator Mata Pelajaran','Kepala Departemen'];
        $obsSubjects = ['Mathematics','English','Science','Bahasa Indonesia','PJOK','Seni'];
        $goalPool = [
            ['title'=>'Adopt formative assessment weekly','desc'=>'Use exit tickets / mini quizzes every lesson'],
            ['title'=>'Complete IB Cat 2 training','desc'=>'Register and complete by end of AY'],
            ['title'=>'Reduce late entries to <2/month','desc'=>'Improve punctuality tracking'],
            ['title'=>'Run parent workshop','desc'=>'Host 1 evening session per semester'],
            ['title'=>'Mentor a junior teacher','desc'=>'Weekly 1-on-1 + classroom visits'],
        ];
        $parentNames = ['Bu Sari','Pak Budi','Mr. Tanaka','Mrs. Lee','Bu Ratna','Pak Wira','Ms. Putri'];
        $commMethods = ['call','whatsapp','email','in_person'];

        // 1. Mentor assignments (first 4 as mentors for next teachers)
        $mentors = $teachers->take(4);
        foreach ($teachers->slice(4)->values() as $i => $t) {
            $t->update(['mentor_id' => $mentors[$i % $mentors->count()]->id]);
        }

        // 2. Substitute pool — 2 pinned subs for first 10 teachers
        $teachers->take(10)->each(function ($t) use ($teachers) {
            $candidates = $teachers->where('id', '!=', $t->id)->shuffle()->take(2)->values();
            $sync = [];
            foreach ($candidates as $i => $c) {
                $sync[$c->id] = ['rank' => $i + 1, 'note' => $i === 0 ? 'Most reliable' : null];
            }
            $t->preferredSubstitutes()->sync($sync);
        });

        foreach ($teachers as $i => $t) {
            // 3. Certifications (2–3 per teacher, mix gov / foreign)
            $picks = collect($certPool)->shuffle()->take(2 + ($i % 2));
            foreach ($picks as $c) {
                TeacherCertification::firstOrCreate(
                    ['teacher_id' => $t->id, 'name' => $c['name']],
                    [
                    'teacher_id'             => $t->id,
                    'name'                   => $c['name'],
                    'issuer'                 => $c['issuer'],
                    'is_government_approved' => $c['gov'],
                    'country'                => $c['country'],
                    'accreditation_no'       => strtoupper(substr(md5($t->id.$c['name']), 0, 8)),
                    'issued_at'              => now()->subMonths(rand(6, 36)),
                    'expires_at'             => rand(0,1) ? now()->addMonths(rand(-2, 24)) : null,
                    ]
                );
            }

            // 4. Clearances (2 per teacher)
            foreach (collect($clearanceTypes)->shuffle()->take(2) as $type) {
                $expires = rand(0,3) === 0 ? now()->subDays(rand(1,60)) : now()->addMonths(rand(2, 24));
                TeacherClearance::firstOrCreate(
                    ['teacher_id' => $t->id, 'type' => $type],
                    [
                    'teacher_id' => $t->id,
                    'type'       => $type,
                    'issuer'     => $type === 'criminal_record' ? 'POLRI' : ($type === 'medical' ? 'RS Mitra' : 'Internal'),
                    'issued_at'  => now()->subMonths(rand(3, 24))->toDateString(),
                    'expires_at' => $expires,
                    'status'     => 'valid',
                    'notes'      => null,
                    ]
                );
            }

            // 5. Attendance (last 20 days for first 12 teachers)
            if ($i < 12) {
                for ($d = 0; $d < 20; $d++) {
                    $date = now()->subDays($d);
                    if ($date->isWeekend()) continue;
                    $status = match(true) {
                        $d % 10 === 0 => 'absent',
                        $d % 7 === 0  => 'late',
                        $d % 13 === 0 => 'leave',
                        default       => 'present',
                    };
                    TeacherAttendance::updateOrCreate(
                        ['teacher_id' => $t->id, 'date' => $date->toDateString()],
                        [
                            'status'        => $status,
                            'check_in_at'   => $status === 'absent' || $status === 'leave' ? null : $date->copy()->setTime($status === 'late' ? 7 : 6, $status === 'late' ? 45 : 50),
                            'check_out_at'  => $status === 'absent' || $status === 'leave' ? null : $date->copy()->setTime(15, 30),
                            'note'          => $status === 'leave' ? 'Annual leave' : null,
                        ]
                    );
                }
            }

            // 6. Employment events (hired + 1-2 randoms)
            TeacherEmploymentEvent::firstOrCreate(
                ['teacher_id' => $t->id, 'event_type' => 'hired'],
                [
                    'event_date' => $t->joined_at ?? now()->subYears(rand(1, 8)),
                    'to_value'   => $t->title ?: 'Guru',
                    'note'       => 'Initial appointment',
                ]
            );
            if ($i % 3 === 0) {
                TeacherEmploymentEvent::create([
                    'teacher_id' => $t->id,
                    'event_type' => 'promoted',
                    'event_date' => now()->subMonths(rand(6, 36)),
                    'from_value' => 'Guru',
                    'to_value'   => $employmentTitles[array_rand($employmentTitles)],
                    'note'       => 'Annual promotion review',
                ]);
            }
            if ($i % 5 === 0) {
                TeacherEmploymentEvent::create([
                    'teacher_id' => $t->id,
                    'event_type' => 'contract_renewed',
                    'event_date' => now()->subMonths(rand(1, 12)),
                    'note'       => '2-year renewal',
                ]);
            }

            // 7. Observations (1-2 per teacher)
            $obsCount = 1 + ($i % 2);
            for ($k = 0; $k < $obsCount; $k++) {
                TeacherObservation::create([
                    'teacher_id'        => $t->id,
                    'observer_id'       => null,
                    'observed_at'       => now()->subDays(rand(7, 90))->setTime(rand(8, 14), 0),
                    'lesson_subject'    => $obsSubjects[array_rand($obsSubjects)],
                    'lesson_class_code' => 'X-IPA-'.rand(1,3),
                    'dimensions'        => [
                        'engagement'        => rand(3, 5),
                        'clarity'           => rand(3, 5),
                        'pacing'            => rand(2, 5),
                        'classroom_mgmt'    => rand(3, 5),
                    ],
                    'strengths'    => 'Strong opening hook; clear learning intentions.',
                    'action_items' => 'Increase wait time after questions; vary question types.',
                    'follow_up_date' => now()->addDays(rand(14, 60)),
                ]);
            }

            // 8. Goals (1-3 per teacher)
            $goalCount = 1 + ($i % 3);
            foreach (collect($goalPool)->shuffle()->take($goalCount) as $g) {
                $progress = rand(0, 100);
                TeacherGoal::create([
                    'teacher_id'    => $t->id,
                    'title'         => $g['title'],
                    'description'   => $g['desc'],
                    'target_date'   => now()->addMonths(rand(1, 6)),
                    'academic_year' => (now()->month >= 7 ? now()->year.'/'.(now()->year+1) : (now()->year-1).'/'.now()->year),
                    'progress'      => $progress,
                    'status'        => $progress >= 100 ? 'done' : ($progress < 25 ? 'at_risk' : 'on_track'),
                ]);
            }

            // 9. Journal entries (1-2 per teacher)
            for ($k = 0; $k < (1 + $i % 2); $k++) {
                TeacherJournalEntry::create([
                    'teacher_id' => $t->id,
                    'entry_date' => now()->subDays(rand(1, 30)),
                    'body'       => 'Today\'s lesson on fractions went well — students engaged in pair-work. Need to revise homework difficulty.',
                    'is_private' => true,
                    'shared_with_mentor' => $k === 0 && $i % 3 === 0,
                ]);
            }

            // 10. Parent communications (2-4 per teacher)
            $commCount = 2 + ($i % 3);
            for ($k = 0; $k < $commCount; $k++) {
                ParentCommunication::create([
                    'teacher_id'     => $t->id,
                    'student_id'     => optional(Student::inRandomOrder()->first())->id,
                    'parent_name'    => $parentNames[array_rand($parentNames)],
                    'contact_method' => $commMethods[array_rand($commMethods)],
                    'occurred_at'    => now()->subDays(rand(1, 60)),
                    'summary'        => 'Discussed progress, behaviour update; parent satisfied.',
                ]);
            }

            // 11. Compensation (1 current record per teacher)
            \App\Models\TeacherCompensation::create([
                'teacher_id'     => $t->id,
                'base_salary'    => 4000000 + ($i * 250000),
                'allowances'     => ['transport' => 500000, 'meal' => 300000, 'role' => 200000 + $i * 50000],
                'currency'       => 'IDR',
                'effective_from' => now()->subMonths(rand(3, 18))->startOfMonth(),
                'notes'          => 'Initial package on file.',
            ]);
        }

        // 12. Sample sensitive approval requests (3 across pipeline)
        $sample = $teachers->take(3);
        if ($sample->count() >= 3) {
            \App\Models\SensitiveApprovalRequest::create([
                'requester_id'      => 1,
                'target_teacher_id' => $sample[0]->id,
                'action_type'       => 'salary_change',
                'payload'           => ['base_salary' => 6500000, 'allowances' => ['transport' => 600000], 'effective_from' => now()->addMonth()->toDateString(), 'notes' => 'Annual raise'],
                'reason'            => 'Annual performance increase.',
                'status'            => 'pending_first',
            ]);
            \App\Models\SensitiveApprovalRequest::create([
                'requester_id'        => 1,
                'target_teacher_id'   => $sample[1]->id,
                'action_type'         => 'title_demotion',
                'payload'             => ['title' => 'guru'],
                'reason'              => 'Workload rebalancing.',
                'status'              => 'pending_second',
                'first_approver_id'   => 1,
                'first_approved_at'   => now()->subDay(),
            ]);
            \App\Models\SensitiveApprovalRequest::create([
                'requester_id'        => 1,
                'target_teacher_id'   => $sample[2]->id,
                'action_type'         => 'contract_terminate',
                'payload'             => ['contract_end' => now()->addMonths(2)->toDateString()],
                'reason'              => 'Contract not renewed; mutual agreement.',
                'status'              => 'approved',
                'first_approver_id'   => 1,
                'first_approved_at'   => now()->subDays(3),
                'second_approver_id'  => 1,
                'second_approved_at'  => now()->subDays(2),
            ]);
        }
    }
}
