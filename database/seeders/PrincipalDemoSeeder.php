<?php

namespace Database\Seeders;

use App\Models\LetterOfIntent;
use App\Models\Teacher;
use App\Models\TeacherObservation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeds demo data for the Principal-module phase 1-8 features:
 *  - Letters of Intent (mix of draft/sent/signed/declined)
 *  - Teacher Observations status workflow (approved/rejected, with reviewer)
 *  - Per-criterion notes on observations
 *
 * Safe to re-run: clears prior demo rows for the chosen academic_year first.
 */
class PrincipalDemoSeeder extends Seeder
{
    public function run(): void
    {
        $principal = User::where('role', 'principal')->first()
            ?? User::whereIn('role', ['vice_principal', 'admin', 'superadmin'])->first()
            ?? User::first();

        if (! $principal) {
            $this->command?->warn('No users found — skipping PrincipalDemoSeeder.');
            return;
        }

        $teachers = Teacher::query()
            ->whereNotNull('name')
            ->orderBy('id')
            ->limit(20)
            ->get();

        if ($teachers->isEmpty()) {
            $this->command?->warn('No teachers found — run TeacherProfileSeeder first.');
            return;
        }

        $this->seedLettersOfIntent($teachers, $principal);
        $this->seedObservationReviews($principal);

        $this->command?->info('PrincipalDemoSeeder: LOI + observation reviews seeded.');
    }

    private function seedLettersOfIntent($teachers, User $principal): void
    {
        $ay = $this->academicYear(+1); // next AY
        LetterOfIntent::where('academic_year', $ay)->delete();

        $bodyTemplate = <<<'TXT'
Dear {{name}},

Following the {{ay_prev}} performance review and your continued contribution to the
{{position}} role, the School Leadership is pleased to formally invite you to
re-commit to Sutomo School for the {{ay}} academic year.

By signing this Letter of Intent you confirm:
  1. Your intention to continue serving as {{position}} for the {{ay}} academic year.
  2. Your acknowledgement that the contract terms (salary, allowances, leave)
     will be issued separately, no later than two weeks after this letter is signed.
  3. Your acceptance of the published academic calendar and duty schedule.

This letter is not a binding contract by itself — it is a non-binding indication
of mutual intent that allows the school to finalise staffing and timetabling.

Kindly review and sign or decline before the deadline. If you have questions,
please contact the Principal's office.

Warm regards,
{{principal_name}}
Principal — Sutomo School
TXT;

        $statuses = ['signed', 'signed', 'signed', 'signed', 'sent', 'sent', 'sent', 'draft', 'declined'];

        foreach ($teachers as $i => $teacher) {
            $status   = $statuses[$i % count($statuses)];
            $position = $teacher->subject ? "{$teacher->subject} Teacher" : ($teacher->dept ?? 'Teacher');

            $body = strtr($bodyTemplate, [
                '{{name}}'           => $teacher->name,
                '{{position}}'       => $position,
                '{{ay}}'             => $ay,
                '{{ay_prev}}'        => $this->academicYear(0),
                '{{principal_name}}' => $principal->name ?? 'School Principal',
            ]);

            $createdAt = now()->subDays(20 - $i)->setTime(9, 0);
            $row = [
                'teacher_id'     => $teacher->id,
                'principal_id'   => $principal->id,
                'academic_year'  => $ay,
                'position'       => $position,
                'body'           => $body,
                'status'         => $status,
                'deadline_at'    => now()->addDays(14),
                'notes'          => null,
                'created_at'     => $createdAt,
                'updated_at'     => $createdAt,
            ];

            if (in_array($status, ['sent', 'signed', 'declined'], true)) {
                $row['sent_at'] = $createdAt->copy()->addHours(2);
            }
            if ($status === 'signed') {
                $row['signed_at']      = $createdAt->copy()->addDays(rand(1, 5));
                $row['signature_text'] = $teacher->name;
                $row['signature_ip']   = '127.0.0.1';
            }
            if ($status === 'declined') {
                $row['decline_reason'] = collect([
                    'Accepted offer elsewhere closer to home.',
                    'Family relocation to another city.',
                    'Pursuing further studies abroad.',
                    'Health reasons require a sabbatical.',
                ])->random();
            }

            LetterOfIntent::create($row);
        }
    }

    private function seedObservationReviews(User $principal): void
    {
        // Add status, reviewer info, and per-criterion notes to existing observations
        // that were created by TeacherProfileSeeder without these fields.
        $observations = TeacherObservation::query()
            ->whereNull('status')
            ->orWhere('status', 'pending')
            ->orderBy('id')
            ->get();

        if ($observations->isEmpty()) {
            return;
        }

        $notePool = [
            'engagement'     => [
                'Students were highly engaged during the discussion phase.',
                'A few students at the back disengaged after the 20-minute mark.',
                'Excellent peer-to-peer interaction throughout the lesson.',
                'Engagement dropped during the worked example — consider a check-for-understanding break.',
            ],
            'clarity'        => [
                'Learning objectives written clearly on the board and revisited at close.',
                'Some technical terms were not unpacked — recommend a vocabulary anchor chart.',
                'Instructions for the group task were crisp and easily followed.',
                'Examples were well-chosen and built on prior knowledge.',
            ],
            'pacing'         => [
                'Strong pacing; transitions between activities were smooth.',
                'The first activity ran over time — adjust the timer or trim instructions.',
                'Pacing slowed mid-lesson; consider chunking the worked example.',
                'Excellent use of the remaining 5 minutes for consolidation.',
            ],
            'classroom_mgmt' => [
                'Clear routines for transitions; students moved efficiently between stations.',
                'Behaviour was well managed with a calm, firm tone.',
                'Two off-task incidents handled quickly and without disruption.',
                'Seating arrangement supported the collaborative task well.',
            ],
        ];

        $strengthsPool = [
            'Strong opening hook; clear learning intentions reviewed at the start.',
            'Excellent questioning technique — used wait time effectively.',
            'Differentiation was visible in the tiered worksheets.',
            'Built rapport with quieter students through targeted check-ins.',
            'Effective use of formative assessment (mini whiteboards).',
        ];

        $actionsPool = [
            'Increase wait time after open questions; aim for 5 seconds.',
            'Add a quick exit ticket to capture misconceptions.',
            'Vary question types — include more "why" and "how" prompts.',
            'Use a visual timer for group work transitions.',
            'Plan an extension task for early finishers.',
        ];

        $i = 0;
        foreach ($observations as $obs) {
            $i++;
            // 60% approved, 20% rejected, 20% remain pending
            $bucket = $i % 5;
            $status = match (true) {
                $bucket < 3 => 'approved',
                $bucket === 3 => 'rejected',
                default => 'pending',
            };

            $notes = [];
            foreach (array_keys($notePool) as $key) {
                $notes[$key] = $notePool[$key][array_rand($notePool[$key])];
            }

            $update = [
                'notes_by_criterion' => $notes,
                'strengths'          => $strengthsPool[array_rand($strengthsPool)],
                'action_items'       => $actionsPool[array_rand($actionsPool)],
                'status'             => $status,
            ];

            if (in_array($status, ['approved', 'rejected'], true)) {
                $update['reviewed_by']  = $principal->id;
                $update['reviewed_at']  = Carbon::parse($obs->observed_at)->addDays(rand(1, 5));
                $update['review_notes'] = $status === 'approved'
                    ? 'Reviewed and approved. Discussed action items with observee at follow-up meeting.'
                    : 'Returned for revision — please add per-criterion evidence before resubmitting.';
            }

            $obs->forceFill($update)->save();
        }
    }

    private function academicYear(int $offsetYears = 0): string
    {
        $now = now();
        $base = $now->month >= 7 ? $now->year : $now->year - 1;
        $start = $base + $offsetYears;
        return $start . '/' . ($start + 1);
    }
}
