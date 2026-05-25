<?php

namespace Database\Seeders;

use App\Models\DutyAssignment;
use App\Models\Teacher;
use App\Models\TeacherDocument;
use App\Models\VoluntaryRequest;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TeacherApprovalsSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = Teacher::limit(20)->get();
        if ($teachers->isEmpty()) {
            $this->command?->warn('No teachers found — skipping TeacherApprovalsSeeder.');
            return;
        }

        $this->seedDocuments($teachers);
        $this->seedVoluntary($teachers);
        $this->seedDuties($teachers);
    }

    private function seedDocuments($teachers): void
    {
        $types = array_keys(TeacherDocument::TYPES);
        $now = Carbon::now();

        foreach ($teachers as $i => $t) {
            // 1 pending doc
            TeacherDocument::create([
                'teacher_id'    => $t->id,
                'type'          => $types[$i % count($types)],
                'label'         => TeacherDocument::TYPES[$types[$i % count($types)]] . ' — ' . $now->year,
                'file_path'     => null,
                'uploaded_at'   => $now->copy()->subDays(rand(1, 12)),
                'expires_at'    => $now->copy()->addYears(rand(1, 5)),
                'status'        => 'pending',
            ]);

            // 1 verified historic doc
            if ($i % 2 === 0) {
                TeacherDocument::create([
                    'teacher_id'    => $t->id,
                    'type'          => 'ktp',
                    'label'         => 'KTP',
                    'uploaded_at'   => $now->copy()->subMonths(8),
                    'status'        => 'verified',
                    'verified_by'   => 'Principal Hartono',
                    'verified_at'   => $now->copy()->subMonths(8)->addDays(2),
                ]);
            }

            // 1 rejected (sparse)
            if ($i % 5 === 0) {
                TeacherDocument::create([
                    'teacher_id'    => $t->id,
                    'type'          => 'other',
                    'label'         => 'Workshop certificate',
                    'uploaded_at'   => $now->copy()->subMonths(2),
                    'status'        => 'rejected',
                    'verified_by'   => 'Principal Hartono',
                    'verified_at'   => $now->copy()->subMonths(2)->addDay(),
                    'note'          => 'Scan unclear — please re-upload a sharper copy.',
                ]);
            }
        }
    }

    private function seedVoluntary($teachers): void
    {
        $programs = [
            'CCA: Robotics Club Mentor',
            'Weekend Tutoring Program',
            'School Camp Chaperone',
            'Sister-School Exchange Visit',
            'PD Workshop: IB Cat 2 Training',
            'Library Reorganization Initiative',
            'Parent-Teacher Conference Facilitator',
            'Mentor Program: New Teacher Buddy',
        ];
        $now = Carbon::now();

        foreach ($teachers->take(12) as $i => $t) {
            VoluntaryRequest::create([
                'teacher_id'   => $t->id,
                'program'      => $programs[$i % count($programs)],
                'reason'       => 'I would like to contribute to this initiative based on my background and interest.',
                'submitted_at' => $now->copy()->subDays(rand(1, 14)),
                'status'       => 'pending',
            ]);
        }

        // a few approved & declined for history
        foreach ($teachers->take(4) as $i => $t) {
            VoluntaryRequest::create([
                'teacher_id'    => $t->id,
                'program'       => $programs[($i + 3) % count($programs)],
                'reason'        => 'Continuing from last semester.',
                'submitted_at'  => $now->copy()->subMonth(),
                'status'        => $i % 2 === 0 ? 'approved' : 'declined',
                'decided_by'    => 'Principal Hartono',
                'decided_at'    => $now->copy()->subMonth()->addDays(2),
                'decision_note' => $i % 2 === 0 ? 'Approved — schedule confirmed.' : 'Capacity full this term.',
            ]);
        }
    }

    private function seedDuties($teachers): void
    {
        $titles = [
            'Morning Gate Duty',
            'Lunch Supervision',
            'After-school Bus Duty',
            'Saturday Open House Greeter',
            'Field-Trip Chaperone — Botanical Garden',
            'Exam Invigilation — Block A',
            'Assembly Coordinator',
            'Library Duty',
        ];
        $locations = ['Main Gate', 'Cafeteria', 'Bus Bay', 'Lobby', 'Off-site', 'Hall A', 'Auditorium', 'Library'];
        $now = Carbon::now();

        foreach ($teachers->take(15) as $i => $t) {
            $start = $now->copy()->addDays(rand(1, 14))->setHour(7 + $i % 8)->setMinute(0);
            DutyAssignment::create([
                'teacher_id'   => $t->id,
                'title'        => $titles[$i % count($titles)],
                'location'     => $locations[$i % count($locations)],
                'starts_at'    => $start,
                'ends_at'      => $start->copy()->addHours(2),
                'assigned_by'  => 'Principal Hartono',
                'status'       => 'pending',
            ]);
        }

        // historical mix
        foreach ($teachers->take(6) as $i => $t) {
            $start = $now->copy()->subDays(rand(3, 30))->setHour(8);
            DutyAssignment::create([
                'teacher_id'    => $t->id,
                'title'         => $titles[($i + 2) % count($titles)],
                'location'      => $locations[$i % count($locations)],
                'starts_at'     => $start,
                'ends_at'       => $start->copy()->addHours(2),
                'assigned_by'   => 'Principal Hartono',
                'status'        => ['accepted', 'declined', 'completed'][$i % 3],
                'responded_at'  => $start->copy()->subDays(2),
                'decline_reason'=> $i % 3 === 1 ? 'Schedule conflict with parent meeting.' : null,
            ]);
        }
    }
}
