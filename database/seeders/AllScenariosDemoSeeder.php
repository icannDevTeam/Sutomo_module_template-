<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AllScenariosDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PrincipalDemoSeeder::class);
        $this->call(TeacherSubstitutionConfigDemoSeeder::class);
        $this->call(SubstitutionTimetableDemoSeeder::class);
        $this->call(SubstituteOfferDemoSeeder::class);
        $this->call(MessagesDemoSeeder::class);
        $this->call(TeacherReviewDemoSeeder::class);
        $this->call(EnrollmentOverviewDemoSeeder::class);
        $this->call(ContractManagementDemoSeeder::class);
        $this->call(PlacementExamDemoSeeder::class);

        $this->command?->info('AllScenariosDemoSeeder: completed.');
    }
}
