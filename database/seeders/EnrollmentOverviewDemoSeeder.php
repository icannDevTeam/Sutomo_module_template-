<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\EnrollmentPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * EnrollmentOverviewDemoSeeder
 *
 * Idempotent: pads the active SMA intake to ~48 applicants with a realistic
 * mix of attendance + placement-exam scores, so the Enrollment Overview page
 * looks great in a live demo. Anchors the 5-day observation window so that
 * "today" is Day 1 of 5.
 *
 * Re-run with:  php artisan db:seed --class=EnrollmentOverviewDemoSeeder
 */
class EnrollmentOverviewDemoSeeder extends Seeder
{
    public function run(): void
    {
        $period = EnrollmentPeriod::where('campus', 'sma')->first()
            ?? EnrollmentPeriod::query()->orderByDesc('opens_at')->first();

        if (! $period) {
            $this->command?->warn('No EnrollmentPeriod found — aborting.');
            return;
        }

        // Re-anchor observation window so Day 1 == today.
        $start = today();
        while ($start->isWeekend()) $start->addDay();
        $period->observation_start_date = $start->toDateString();
        $period->observation_days       = 5;
        $period->exam_venue             = $period->exam_venue ?: 'Room 201 & 202';
        $period->save();

        $dates = $this->buildDates($start, 5);
        $todayKey = $dates[0];

        $existingCount = Application::where('enrollment_period_id', $period->id)
            ->whereIn('status', Application::ONBOARDING_STATUSES)
            ->count();

        $maxExistingCode = (int) (Application::query()
            ->where('code', 'like', 'APP-EO-%')
            ->selectRaw("MAX(CAST(SUBSTR(code, 8) AS UNSIGNED)) as max_no")
            ->value('max_no') ?? 0);

        $target = 48;
        $toCreate = max(0, $target - $existingCount);

        $first = ['Andi','Dian','Fajar','Indah','Muhammad','Siti','Bagas','Cici','Doni','Erika','Farhan','Gita','Hadi','Intan','Joko','Karina','Lukman','Maya','Nadia','Oka','Putri','Qori','Rama','Sinta','Tegar','Umar','Vania','Wahyu','Xander','Yusuf','Zahra','Bayu','Citra','Devi','Eka','Galih','Hilda','Ilham','Jihan','Kemal','Lala','Mira','Nico','Olive','Pandu','Reza','Sari','Tomi'];
        $last  = ['Nugroho','Pratiwi','Hidayat','Ratnasari','Prasetyo','Wulandari','Saputra','Halim','Suryadi','Anggraini','Permana','Kusuma','Maulana','Sitorus','Tanjung','Iskandar','Wijaya','Rahman','Setiawan','Putra','Lestari'];

        for ($i = 0; $i < $toCreate; $i++) {
            $name = $first[$i % count($first)] . ' ' . $last[($i * 3) % count($last)];
            $codeNo = $maxExistingCode + $i + 1;
            Application::create([
                'code'                 => 'APP-EO-' . str_pad((string) $codeNo, 4, '0', STR_PAD_LEFT),
                'name'                 => $name,
                'gender'               => $i % 2 ? 'F' : 'M',
                'dob'                  => now()->subYears(16)->subDays(random_int(1, 300))->toDateString(),
                'religion'             => 'Islam',
                'city'                 => 'Medan',
                'parent_name'          => 'Bpk./Ibu ' . $last[($i * 5) % count($last)],
                'parent_phone'         => '+62 81' . str_pad((string) random_int(10000000, 99999999), 8, '0'),
                'parent_email'         => 'parent' . ($existingCount + $i + 1) . '@example.test',
                'campus'               => 'sma',
                'unit'                 => 'CMP-001',
                'grade'                => (string) random_int(10, 12),
                'stream'               => random_int(0, 1) ? 'ipa' : 'ips',
                'enrollment_period_id' => $period->id,
                'applicant_type'       => 'new',
                'status'               => 'observing',
                'applied_at'           => now()->subDays(random_int(15, 45))->toDateString(),
                'payment_status'       => 'paid',
                'payment_method'       => 'transfer',
                'payment_amount'       => 300000,
                'payment_paid_at'      => now()->subDays(random_int(10, 30)),
                'meta'                 => [],
            ]);
        }

        // Refresh full cohort and distribute attendance + scores deterministically.
        $cohort = Application::where('enrollment_period_id', $period->id)
            ->whereIn('status', Application::ONBOARDING_STATUSES)
            ->orderBy('id')
            ->get();

        $total = $cohort->count();
        // Target: 43 present today, 5 absent. Adapt if cohort smaller.
        $absentToday = (int) min(5, max(0, round($total * 0.10)));
        $presentToday = $total - $absentToday;

        // Target: 28 scores submitted, 20 pending.
        $submittedTarget = (int) min(round($total * 0.58), $total);

        $teacher = 'Ibu Lisa Wijaya';

        $idx = 0;
        foreach ($cohort as $app) {
            $meta = $app->meta ?? [];

            // Build attendance for all 5 days. Today = $dates[0]. Future days = pending.
            $days = [];
            foreach ($dates as $di => $dKey) {
                if ($di === 0) {
                    // Spread absent over the last $absentToday rows (alphabetical-ish via id order).
                    $isAbsent = $idx >= ($total - $absentToday);
                    $days[$dKey] = [
                        'present' => ! $isAbsent,
                        'by'      => $teacher,
                        'at'      => Carbon::parse($dKey)->setTime(8, 5 + ($idx % 20))->toIso8601String(),
                    ];
                } else {
                    $days[$dKey] = ['present' => null, 'by' => null, 'at' => null];
                }
            }
            $meta['observation'] = ['start_date' => $dates[0], 'days' => $days];

            // Placement score: first $submittedTarget rows get a score.
            if ($idx < $submittedTarget) {
                $base = 60 + (($idx * 13) % 40); // 60..99 spread
                $score = round($base + (($idx % 5) * 0.5), 1);
                $app->placement_score = $score;
                $meta['principal']['score_sent_at'] = now()->subHours(random_int(1, 12))->toIso8601String();
            } else {
                $app->placement_score = null;
                unset($meta['principal']['score_sent_at']);
            }

            // If absent today, clear the score (matches the screenshot's "Absent · —").
            if ($idx >= ($total - $absentToday)) {
                $app->placement_score = null;
            }

            $app->meta = $meta;
            $app->save();
            $idx++;
        }

        $this->command?->info("Enrollment Overview demo: period #{$period->id} window {$dates[0]} → " . end($dates) . ", cohort={$total}, present={$presentToday}, absent={$absentToday}, submitted=~{$submittedTarget}.");
    }

    private function buildDates(Carbon $start, int $days): array
    {
        $out = [];
        $cursor = $start->copy();
        for ($i = 0; $i < $days; $i++) {
            while ($cursor->isWeekend()) $cursor->addDay();
            $out[] = $cursor->toDateString();
            $cursor->addDay();
        }
        return $out;
    }
}
