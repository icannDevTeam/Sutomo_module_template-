<?php

namespace Database\Seeders;

use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

class ProbationRenewalDemoSeeder extends ContractManagementDemoSeeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            foreach ([
                ['PR-DEMO-P01', 45, 2, 1, '45 days left — partially observed'],
                ['PR-DEMO-P02', 25, 4, 2, '65 days left — all minimums met'],
                ['PR-DEMO-P03', 81, 1, 0, '9 days left — urgent'],
                ['PR-DEMO-P04', 60, 3, 2, '30 days left — 1 supervision short'],
            ] as [$code, $days, $supervisions, $peers, $label]) {
                $teacher = $this->scenarioTeacher($code, 'Probation');
                if ($teacher->contracts()->exists()) {
                    $this->command?->info("Skipping {$code}: contract already exists.");
                    continue;
                }

                $this->probationScenario($teacher->id, $days, $supervisions, $peers, $label);
            }

            foreach ([
                ['PR-DEMO-R01', 'pkwt_1', 77, 'fresh'],
                ['PR-DEMO-R02', 'pkwt_2', 14, 'recommended'],
                ['PR-DEMO-R03', 'pkwt_2', 45, 'not_renew'],
                ['PR-DEMO-R04', 'pkwt_3', 257, 'submitted'],
                ['PR-DEMO-R05', 'pkwt_1', 257, 'submitted'],
            ] as [$code, $type, $days, $stage]) {
                $teacher = $this->scenarioTeacher($code, 'Renewal');
                if ($teacher->contracts()->exists()) {
                    $this->command?->info("Skipping {$code}: contract already exists.");
                    continue;
                }

                $this->renewalScenario($teacher->id, $type, $days, $stage);
                $teacher->contracts()->update(['recommendation_letter_path' => null]);
            }
        });

        $this->command?->info('ProbationRenewalDemoSeeder: complete. Existing records and LOIs were preserved.');
    }

    private function scenarioTeacher(string $code, string $group): Teacher
    {
        return Teacher::firstOrCreate(
            ['code' => $code],
            ['name' => "{$group} {$code} (Demo)", 'campus' => 'sma', 'status' => 'pkwt_1']
        );
    }
}
