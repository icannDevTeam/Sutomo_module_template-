<?php

namespace Database\Seeders;

use App\Models\QueryLetter;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\TeacherClearance;
use App\Models\TeacherGoal;
use App\Models\TeacherLeave;
use App\Models\TeacherNote;
use App\Models\TeacherObservation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeds 6 archetype teachers so every card flag/KPI lights up:
 *   1. Star Performer       -> auto Ready (green)
 *   2. Steady Developer     -> auto Developing (amber)
 *   3. On Watch             -> auto Not Ready (red, open query + low attendance)
 *   4. Contract Renewal Due -> Developing + contract ending in ~45 days
 *   5. On Leave Today       -> approved leave covering today + pinned note
 *   6. Manual Override      -> auto would be Ready, principal flagged Not Ready
 *
 * Also exercises: missing-docs flag, due-for-review (>12mo), saved-view filters.
 *
 * Safe to re-run: deletes its own seeded rows by `code` prefix before recreating.
 */
class TeacherReviewDemoSeeder extends Seeder
{
    private const CODE_PREFIX = 'TR-DEMO-';

    public function run(): void
    {
        $principal = User::where('role', 'principal')->first()
            ?? User::where('role', 'admin')->first()
            ?? User::first();

        $this->wipePrevious();

        $now = Carbon::now();

        $archetypes = [
            [
                'code'         => self::CODE_PREFIX . '01',
                'name'         => 'Anita Wijaya, S.Pd (Demo)',
                'gender'       => 'female',
                'subject'      => 'Mathematics',
                'dept'         => 'STEM',
                'campus'       => 'Sutomo 1',
                'status'       => 'permanent',
                'employment'   => 'Full time',
                'joined_at'    => $now->copy()->subYears(8)->subMonths(2),
                'last_review'  => $now->copy()->subMonths(4),
                'attendance'   => 95,           // present rate
                'obs'          => [3.8, 3.9, 3.7],
                'goalsProgress'=> [85, 90, 95],
                'queries'      => [],
                'leaves'       => [],
                'clearances'   => 'valid',
                'pinnedNote'   => null,
                'note'         => "Consistent high performer. Mentors two probationary teachers.",
                'manual'       => null,
            ],
            [
                'code'         => self::CODE_PREFIX . '02',
                'name'         => 'Bambang Pratama, M.Pd (Demo)',
                'gender'       => 'male',
                'subject'      => 'Physics',
                'dept'         => 'STEM',
                'campus'       => 'Sutomo 1',
                'status'       => 'permanent',
                'employment'   => 'Full time',
                'joined_at'    => $now->copy()->subYears(2)->subMonths(3),
                'last_review'  => $now->copy()->subMonths(8),
                'attendance'   => 86,
                'obs'          => [3.1, 3.2, 3.0],
                'goalsProgress'=> [50, 60, 55],
                'queries'      => [],
                'leaves'       => [],
                'clearances'   => 'valid',
                'pinnedNote'   => null,
                'note'         => "Solid; pacing needs work — assigned to Anita for mentoring.",
                'manual'       => null,
            ],
            [
                'code'         => self::CODE_PREFIX . '03',
                'name'         => 'Citra Halim, S.Pd (Demo)',
                'gender'       => 'female',
                'subject'      => 'English',
                'dept'         => 'Languages',
                'campus'       => 'Sutomo 2',
                'status'       => 'probation',
                'employment'   => 'Full time',
                'joined_at'    => $now->copy()->subMonths(6),
                'last_review'  => null,
                'attendance'   => 70,
                'obs'          => [2.3, 2.6],
                'goalsProgress'=> [20, 15],
                'queries'      => [
                    ['title' => 'Unexplained absence Apr 18', 'body' => 'Please explain absence on Apr 18 without prior notice.', 'status' => 'sent', 'days_ago' => 30],
                ],
                'leaves'       => [],
                'clearances'   => 'pending',
                'pinnedNote'   => 'Performance improvement plan in progress.',
                'note'         => null,
                'manual'       => null,
            ],
            [
                'code'         => self::CODE_PREFIX . '04',
                'name'         => 'Dewi Sartika, S.S (Demo)',
                'gender'       => 'female',
                'subject'      => 'Indonesian',
                'dept'         => 'Languages',
                'campus'       => 'Sutomo 2',
                'status'       => 'contract',
                'employment'   => 'Contract',
                'joined_at'    => $now->copy()->subYears(2),
                'contract_end' => $now->copy()->addDays(45),
                'last_review'  => $now->copy()->subMonths(11),
                'attendance'   => 88,
                'obs'          => [3.3, 3.4],
                'goalsProgress'=> [70, 65],
                'queries'      => [],
                'leaves'       => [],
                'clearances'   => 'valid',
                'pinnedNote'   => null,
                'note'         => "Contract renewal decision due. Strong candidate for permanent.",
                'manual'       => null,
            ],
            [
                'code'         => self::CODE_PREFIX . '05',
                'name'         => 'Eko Nugroho, S.Pd (Demo)',
                'gender'       => 'male',
                'subject'      => 'History',
                'dept'         => 'Humanities',
                'campus'       => 'Sutomo 1',
                'status'       => 'leave',
                'employment'   => 'Full time',
                'joined_at'    => $now->copy()->subYears(5),
                'last_review'  => $now->copy()->subMonths(3),
                'attendance'   => 92,
                'obs'          => [3.5, 3.6],
                'goalsProgress'=> [60],
                'queries'      => [],
                'leaves'       => [
                    ['type' => 'sick', 'days_ago_start' => 2, 'days_ahead_end' => 5],
                ],
                'clearances'   => 'valid',
                'pinnedNote'   => 'Recovering from minor surgery; back on the 10th.',
                'note'         => null,
                'manual'       => null,
            ],
            [
                'code'         => self::CODE_PREFIX . '06',
                'name'         => 'Fitri Andayani, M.Pd (Demo)',
                'gender'       => 'female',
                'subject'      => 'Biology',
                'dept'         => 'STEM',
                'campus'       => 'Sutomo 3',
                'status'       => 'permanent',
                'employment'   => 'Full time',
                'joined_at'    => $now->copy()->subYears(6),
                'last_review'  => $now->copy()->subMonths(2),
                'attendance'   => 96,
                'obs'          => [3.9, 3.8, 3.9],
                'goalsProgress'=> [90, 92, 88],
                'queries'      => [],
                'leaves'       => [],
                'clearances'   => 'valid',
                'pinnedNote'   => null,
                'note'         => "Excellent metrics but recurring parent complaints under review.",
                'manual'       => [
                    'level' => 'not_ready',
                    'note'  => 'Holding promotion pending HR review of parent complaints (Mar–May).',
                ],
            ],
        ];

        foreach ($archetypes as $a) {
            $teacher = Teacher::create([
                'code'        => $a['code'],
                'name'        => $a['name'],
                'gender'      => $a['gender'],
                'subject'     => $a['subject'],
                'dept'        => $a['dept'],
                'campus'      => $a['campus'],
                'status'      => $a['status'],
                'employment'  => $a['employment'],
                'joined_at'   => $a['joined_at'],
                'contract_end'=> $a['contract_end'] ?? null,
                'last_review' => $a['last_review'],
                'email'       => strtolower(str_replace([' ', ',', '.'], ['', '', ''], explode(' ', $a['name'])[0])) . '+demo@example.com',
            ]);

            $this->seedAttendance($teacher, $a['attendance']);
            $this->seedObservations($teacher, $a['obs'], $principal);
            $this->seedGoals($teacher, $a['goalsProgress']);

            foreach ($a['queries'] as $q) {
                QueryLetter::create([
                    'teacher_id' => $teacher->id,
                    'issued_by'  => $principal?->id,
                    'title'      => $q['title'],
                    'body'       => $q['body'],
                    'issued_at'  => $now->copy()->subDays($q['days_ago']),
                    'status'     => $q['status'],
                ]);
            }

            foreach ($a['leaves'] as $l) {
                TeacherLeave::create([
                    'teacher_id' => $teacher->id,
                    'type'       => $l['type'],
                    'starts_at'  => $now->copy()->subDays($l['days_ago_start']),
                    'ends_at'    => $now->copy()->addDays($l['days_ahead_end']),
                    'reason'     => 'Demo leave',
                    'status'     => 'approved',
                    'decided_by' => $principal?->id,
                    'decided_at' => $now->copy()->subDays($l['days_ago_start'] + 1),
                ]);
            }

            TeacherClearance::create([
                'teacher_id' => $teacher->id,
                'type'       => 'criminal_record',
                'issuer'     => 'POLRI',
                'issued_at'  => $a['clearances'] === 'pending' ? null : $now->copy()->subMonths(6),
                'expires_at' => $a['clearances'] === 'pending' ? null : $now->copy()->addMonths(18),
                'status'     => $a['clearances'],
            ]);

            if ($a['pinnedNote']) {
                TeacherNote::create([
                    'teacher_id' => $teacher->id,
                    'author_id'  => $principal?->id,
                    'body'       => $a['pinnedNote'],
                    'pinned'     => true,
                ]);
            }
            if ($a['note']) {
                TeacherNote::create([
                    'teacher_id' => $teacher->id,
                    'author_id'  => $principal?->id,
                    'body'       => $a['note'],
                    'pinned'     => false,
                ]);
            }

            if ($a['manual']) {
                $teacher->update([
                    'promotion_readiness'        => $a['manual']['level'],
                    'promotion_readiness_note'   => $a['manual']['note'],
                    'promotion_readiness_set_by' => $principal?->id,
                    'promotion_readiness_set_at' => $now,
                ]);
            }
        }

        $this->command?->info('Seeded ' . count($archetypes) . ' Teacher Review demo teachers (code prefix ' . self::CODE_PREFIX . ').');
    }

    private function wipePrevious(): void
    {
        $ids = Teacher::where('code', 'like', self::CODE_PREFIX . '%')->pluck('id');
        if ($ids->isEmpty()) return;

        TeacherAttendance::whereIn('teacher_id', $ids)->delete();
        TeacherObservation::whereIn('teacher_id', $ids)->delete();
        TeacherGoal::whereIn('teacher_id', $ids)->delete();
        TeacherLeave::whereIn('teacher_id', $ids)->delete();
        TeacherClearance::whereIn('teacher_id', $ids)->delete();
        QueryLetter::whereIn('teacher_id', $ids)->delete();
        TeacherNote::whereIn('teacher_id', $ids)->delete();
        Teacher::whereIn('id', $ids)->delete();
    }

    private function seedAttendance(Teacher $t, int $presentPct): void
    {
        // 90 weekdays back-filled. Distribute statuses to roughly match presentPct.
        $today = Carbon::now()->startOfDay();
        $weekdays = [];
        $d = $today->copy()->subDays(120);
        while (count($weekdays) < 90 && $d->lt($today)) {
            if (! $d->isWeekend()) $weekdays[] = $d->copy();
            $d->addDay();
        }

        $total      = count($weekdays);
        $presentN   = (int) round($total * $presentPct / 100);
        $lateN      = (int) round($total * 0.05);
        $absentN    = max(0, $total - $presentN - $lateN);

        $statuses = array_merge(
            array_fill(0, $presentN, 'present'),
            array_fill(0, $lateN,    'late'),
            array_fill(0, $absentN,  'absent'),
        );
        shuffle($statuses);

        foreach ($weekdays as $i => $date) {
            $status = $statuses[$i] ?? 'present';
            TeacherAttendance::updateOrCreate(
                ['teacher_id' => $t->id, 'date' => $date->toDateString()],
                [
                    'status'       => $status,
                    'check_in_at'  => in_array($status, ['absent', 'leave']) ? null : $date->copy()->setTime($status === 'late' ? 7 : 6, $status === 'late' ? 45 : 50),
                    'check_out_at' => in_array($status, ['absent', 'leave']) ? null : $date->copy()->setTime(15, 30),
                ],
            );
        }
    }

    private function seedObservations(Teacher $t, array $scores, ?User $observer): void
    {
        $base = Carbon::now()->subDays(60);
        foreach ($scores as $i => $score) {
            TeacherObservation::create([
                'teacher_id'  => $t->id,
                'observer_id' => $observer?->id,
                'observed_at' => $base->copy()->addDays($i * 18),
                'dimensions'  => [
                    'engagement'     => $score,
                    'clarity'        => $score,
                    'pacing'         => max(1, $score - 0.2),
                    'classroom_mgmt' => min(4, $score + 0.1),
                ],
                'strengths'   => 'Demo observation',
                'status'      => 'completed',
            ]);
        }
    }

    private function seedGoals(Teacher $t, array $progresses): void
    {
        $base = Carbon::now()->subMonths(3);
        $titles = ['Differentiated instruction', 'Formative assessment toolkit', 'Parent engagement plan'];
        foreach ($progresses as $i => $p) {
            TeacherGoal::create([
                'teacher_id'  => $t->id,
                'title'       => $titles[$i] ?? ('Goal ' . ($i + 1)),
                'target_date' => $base->copy()->addMonths(6 + $i),
                'progress'    => $p,
                'status'      => $p >= 80 ? 'on_track' : ($p >= 40 ? 'on_track' : 'at_risk'),
            ]);
        }
    }
}
