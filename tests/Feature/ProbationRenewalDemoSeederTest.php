<?php

namespace Tests\Feature;

use App\Models\LetterOfIntent;
use App\Models\Teacher;
use App\Models\TeacherContract;
use App\Models\TeacherObservation;
use Database\Seeders\ProbationRenewalDemoSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProbationRenewalDemoSeederTest extends TestCase
{
    use DatabaseTransactions;

    public function test_seeder_adds_only_probation_and_renewal_and_is_repeatable(): void
    {
        $teachersBefore = Teacher::count();
        $contractsBefore = TeacherContract::count();
        $observationsBefore = TeacherObservation::count();
        $loisBefore = LetterOfIntent::orderBy('id')->get()->toArray();
        $existingTeachers = Teacher::orderBy('id')->get()->toArray();
        $existingContracts = TeacherContract::orderBy('id')->get()->toArray();

        $this->seed(ProbationRenewalDemoSeeder::class);

        $demoTeachers = Teacher::where('code', 'like', 'PR-DEMO-%')->get();
        $this->assertCount(9, $demoTeachers);
        $this->assertSame($teachersBefore + 9, Teacher::count());
        $this->assertSame($contractsBefore + 9, TeacherContract::count());
        $this->assertSame(4, TeacherContract::whereIn('teacher_id', $demoTeachers->pluck('id'))->whereNull('probation_decision')->whereNotNull('probation_ends_at')->count());
        $this->assertSame(2, TeacherContract::whereIn('teacher_id', $demoTeachers->pluck('id'))->whereNotNull('submitted_to_yayasan_at')->count());
        $this->assertSame(0, TeacherContract::whereIn('teacher_id', $demoTeachers->pluck('id'))->whereNotNull('recommendation_letter_path')->count());
        $observationsAfter = TeacherObservation::count();
        $this->assertGreaterThan($observationsBefore, $observationsAfter);

        $this->seed(ProbationRenewalDemoSeeder::class);

        $this->assertSame($teachersBefore + 9, Teacher::count());
        $this->assertSame($contractsBefore + 9, TeacherContract::count());
        $this->assertSame($observationsAfter, TeacherObservation::count());
        $this->assertSame($loisBefore, LetterOfIntent::orderBy('id')->get()->toArray());
        $this->assertSame($existingTeachers, Teacher::whereNotIn('id', $demoTeachers->pluck('id'))->orderBy('id')->get()->toArray());
        $this->assertSame($existingContracts, TeacherContract::whereNotIn('teacher_id', $demoTeachers->pluck('id'))->orderBy('id')->get()->toArray());
    }
}
