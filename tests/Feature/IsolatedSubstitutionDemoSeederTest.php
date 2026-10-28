<?php

namespace Tests\Feature;

use App\Models\DutyAssignment;
use App\Models\LeaveType;
use App\Models\Teacher;
use App\Models\TeacherLeave;
use App\Models\TimetableDefinition;
use App\Models\TimetableLesson;
use App\Models\TimetableSnapshot;
use App\Services\Timetable\TeacherSchedule;
use App\Support\SubstitutionTimetable;
use Carbon\CarbonImmutable;
use Database\Seeders\IsolatedSubstitutionDemoSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class IsolatedSubstitutionDemoSeederTest extends TestCase
{
    use DatabaseTransactions;

    public function test_demo_states_preservation_and_reruns(): void
    {
        $definition = TimetableDefinition::find(TeacherSchedule::activeDefinitionId());
        if (! $definition) {
            $definition = TimetableDefinition::create(['term' => 'TEST', 'school_unit' => 'ALL', 'status' => 'published']);
        }
        $definition->update(['status' => 'published']);
        LeaveType::where('key', 'sick')->update(['requires_substitute' => true]);
        $before = $this->snapshot();

        $this->seed(IsolatedSubstitutionDemoSeeder::class);
        $week = CarbonImmutable::today()->startOfWeek();
        $data = SubstitutionTimetable::buildWindow($week, $week->addDays(5), search: 'Substitution Demo');
        $this->assertCount(30, collect($data['cells'])->where('source', 'leave'));
        foreach (['confirmed', 'awaiting', 'no_handoff', 'declined', 'uncovered'] as $state) {
            $this->assertSame(6, collect($data['cells'])->where('state', $state)->count());
        }
        $this->assertSame(3, collect($data['cells'])->where('state', 'duty')->count());
        $this->assertContains('pending', collect($data['attention'])->pluck('state')->all());

        foreach ($before as $model => $rows) {
            $this->assertSame($rows, $model::whereIn('id', array_column($rows, 'id'))->orderBy('id')->get()->toArray());
        }
        $leave = TeacherLeave::where('reason', 'like', 'Isolated substitution demo: pending%')->firstOrFail();
        $leave->update(['status' => 'rejected']);
        $after = $this->snapshot();
        $this->seed(IsolatedSubstitutionDemoSeeder::class);
        $this->assertSame($after, $this->snapshot());
    }

    public function test_missing_published_timetable_creates_nothing(): void
    {
        TimetableSnapshot::query()->delete();
        TimetableDefinition::query()->update(['status' => 'draft']);
        $before = $this->snapshot();
        try {
            $this->seed(IsolatedSubstitutionDemoSeeder::class);
            $this->fail('Expected missing published timetable to be refused.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('published timetable is required', $e->getMessage());
        }
        $this->assertSame($before, $this->snapshot());
    }

    public function test_conflicting_teacher_code_rolls_back_partial_demo_records(): void
    {
        $definition = TimetableDefinition::find(TeacherSchedule::activeDefinitionId());
        if (! $definition) {
            $definition = TimetableDefinition::create(['term' => 'TEST', 'school_unit' => 'ALL', 'status' => 'published']);
        }
        $definition->update(['status' => 'published']);
        LeaveType::where('key', 'sick')->update(['requires_substitute' => true]);
        Teacher::create(['code' => 'SUB-DEMO-AWAITING', 'name' => 'Existing school teacher', 'campus' => 'sma', 'status' => 'pkwt_1']);
        $before = $this->snapshot();

        try {
            $this->seed(IsolatedSubstitutionDemoSeeder::class);
            $this->fail('Expected conflicting teacher code to be refused.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('already belongs to a different teacher', $e->getMessage());
        }

        $this->assertSame($before, $this->snapshot());
    }

    private function snapshot(): array
    {
        $data = [];
        foreach ([Teacher::class, TeacherLeave::class, DutyAssignment::class, TimetableLesson::class, TimetableDefinition::class] as $model) {
            $data[$model] = $model::orderBy('id')->get()->toArray();
        }

        return $data;
    }
}
