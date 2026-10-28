<?php

namespace Tests\Feature;

use App\Filament\Principal\Pages\SubstitutionTimetable as Page;
use App\Models\DutyAssignment;
use App\Models\SubstituteOffer;
use App\Models\Teacher;
use App\Models\TeacherLeave;
use App\Models\TimetableLesson;
use App\Models\User;
use App\Services\Timetable\TeacherSchedule;
use App\Support\SubstituteBroadcaster;
use App\Support\SubstitutionTimetable as Builder;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class SubstitutionTimetableTest extends TestCase
{
    use DatabaseTransactions;

    private const CAMPUS = 'zz-test';

    private CarbonImmutable $mon;
    private int $defId;
    private User $principal;
    /** @var array<string, Teacher> */
    private array $t = [];

    protected function setUp(): void
    {
        parent::setUp();

        // A far-future week so existing demo data can't interfere.
        $this->mon = CarbonImmutable::parse('2031-03-03'); // Monday
        $this->defId = TeacherSchedule::activeDefinitionId()
            ?? \App\Models\TimetableDefinition::create(['term' => 'TEST', 'school_unit' => 'ALL', 'status' => 'published'])->id;

        foreach (['absA', 'absB', 'absC', 'absD', 'absE', 'subA', 'subB', 'subC'] as $k) {
            $this->t[$k] = Teacher::create([
                'code' => 'ZZ-' . $k . '-' . Str::random(4), 'name' => 'ZZ ' . $k,
                'status' => 'tetap', 'campus' => self::CAMPUS, 'subject' => 'Math',
            ]);
        }
        // Each absent teacher teaches period I + II on Monday.
        foreach (['absA', 'absB', 'absC', 'absD', 'absE'] as $k) {
            foreach (['I', 'II'] as $p) {
                TimetableLesson::create([
                    'definition_id' => $this->defId, 'teacher_ref' => $this->t[$k]->code,
                    'class_code' => '7-' . $k, 'subject' => 'Math', 'day' => 'SENIN',
                    'period' => $p, 'session' => 'pagi', 'room' => 'R1',
                ]);
            }
        }

        $this->principal = User::create([
            'name' => 'Principal ' . Str::random(4), 'email' => Str::random(8) . '@test.local',
            'password' => bcrypt('x'), 'role' => 'principal', 'campus' => self::CAMPUS,
        ]);
    }

    private function leave(string $teacher, string $status, ?string $sub = null): TeacherLeave
    {
        return TeacherLeave::create([
            'teacher_id' => $this->t[$teacher]->id, 'type' => 'sick',
            'starts_at' => $this->mon->toDateString(), 'ends_at' => $this->mon->toDateString(),
            'status' => $status, 'substitute_teacher_id' => $sub ? $this->t[$sub]->id : null,
        ]);
    }

    private function duty(string $teacher, string $status, array $extra = []): DutyAssignment
    {
        return DutyAssignment::create(array_merge([
            'teacher_id' => $this->t[$teacher]->id, 'title' => 'Cover', 'recurrence' => 'once',
            'starts_at' => $this->mon, 'ends_at' => $this->mon, 'status' => $status,
        ], $extra));
    }

    private function build(?string $filter = null): array
    {
        return Builder::buildWindow($this->mon, $this->mon->addDays(5), self::CAMPUS, $filter);
    }

    public function test_cells_carry_the_right_coverage_state(): void
    {
        $this->leave('absA', 'approved', 'subA');                 // no hand-off
        $this->leave('absB', 'approved', 'subB');
        $this->duty('subB', 'pending');                            // awaiting
        $this->leave('absC', 'approved', 'subC');
        $this->duty('subC', 'accepted');                           // confirmed
        $this->leave('absD', 'approved');                          // uncovered
        $this->leave('absE', 'pending');                           // attention only

        $data = $this->build();
        $byOriginal = collect($data['cells'])->where('source', 'leave')->groupBy('original');

        $this->assertSame(['no_handoff'], $byOriginal['ZZ absA']->pluck('state')->unique()->values()->all());
        $this->assertSame(['awaiting'], $byOriginal['ZZ absB']->pluck('state')->unique()->values()->all());
        $this->assertSame(['confirmed'], $byOriginal['ZZ absC']->pluck('state')->unique()->values()->all());
        $this->assertSame(['uncovered'], $byOriginal['ZZ absD']->pluck('state')->unique()->values()->all());
        $this->assertArrayNotHasKey('ZZ absE', $byOriginal->all());

        $this->assertSame(8, $data['summary']['lessons_total']);
        $this->assertSame(2, $data['summary']['confirmed']);
        $this->assertSame(2, $data['summary']['awaiting']);
        $this->assertSame(2, $data['summary']['uncovered']);
        $this->assertSame(4, $data['summary']['gaps_count']);
        $this->assertSame(1, $data['summary']['pending_leaves']);

        $attention = collect($data['attention'])->pluck('state', 'teacher');
        $this->assertSame('uncovered', $attention['ZZ absD']);
        $this->assertSame('no_handoff', $attention['ZZ absA']);
        $this->assertSame('pending', $attention['ZZ absE']);
        $this->assertSame('uncovered', $data['attention'][0]['state'], 'worst first');

        // Hand-off duties are folded into the leave, not shown as standalone duties.
        $this->assertSame(0, $data['summary']['duties_count']);

        $this->assertSame(['ZZ absD'], collect($this->build('action')['cells'])
            ->where('state', 'uncovered')->pluck('original')->unique()->values()->all());
        $this->assertCount(2, $this->build('confirmed')['cells']);
    }

    public function test_weekly_duties_are_not_mistaken_for_handoffs(): void
    {
        $this->leave('absA', 'approved', 'subA');
        $this->duty('subA', 'accepted', [
            'title' => 'Gate supervision', 'recurrence' => 'weekly', 'days_of_week' => ['1', '3'],
            'ends_at' => $this->mon->addWeeks(4),
        ]);

        $data = $this->build();
        $this->assertSame('no_handoff', collect($data['cells'])->firstWhere('source', 'leave')['state']);

        // String day numbers still match; shown on Mon + Wed only.
        $dutyDates = collect($data['cells'])->where('source', 'duty')->pluck('date')->all();
        $this->assertSame([$this->mon->toDateString(), $this->mon->addDays(2)->toDateString()], $dutyDates);
    }

    public function test_declined_handoff_and_open_ended_duty(): void
    {
        $this->leave('absA', 'approved', 'subA');
        $this->duty('subA', 'declined');
        $this->duty('subB', 'pending', ['title' => 'Exam room', 'starts_at' => $this->mon->addDays(1)->setTime(8, 0), 'ends_at' => null]);

        $data = $this->build();
        $this->assertSame('declined', collect($data['cells'])->firstWhere('source', 'leave')['state']);

        $duty = collect($data['cells'])->firstWhere('source', 'duty');
        $this->assertSame('Exam room', $duty['subject']);
        $this->assertSame($this->mon->addDays(1)->toDateString(), $duty['date']);
    }

    public function test_leave_starting_on_last_day_of_window_is_included(): void
    {
        $this->leave('absA', 'approved', 'subA');

        // Single-day window == leave start date (stored as 'Y-m-d 00:00:00').
        $data = Builder::buildWindow($this->mon, $this->mon, self::CAMPUS);

        $this->assertSame(2, $data['summary']['lessons_total']);
        $this->assertSame('no_handoff', $data['attention'][0]['state']);
    }

    public function test_campus_unit_and_search_filters(): void
    {
        $this->t['absA']->update(['campus' => 'Sutomo 9']);
        $this->t['absB']->update(['campus' => 'smp']);
        $this->t['subB']->update(['campus' => 'smp']);
        $this->leave('absA', 'approved', 'subA');
        $this->leave('absB', 'approved', 'subB');

        $originals = fn (array $args) => collect(Builder::buildWindow($this->mon, $this->mon->addDays(5), ...$args)['cells'])
            ->pluck('original')->unique()->values()->all();

        $this->assertSame(['ZZ absA'], $originals(['campus' => 'Sutomo 9']));
        $this->assertSame(['ZZ absB'], $originals(['unit' => 'smp']));
        $this->assertSame(['ZZ absB'], $originals(['unit' => 'SMP']));
        $this->assertSame(['ZZ absA'], $originals(['campus' => self::CAMPUS, 'search' => 'suba']));   // covering teacher
        $this->assertSame(['ZZ absB'], $originals(['unit' => 'smp', 'search' => 'ABSB']));           // absent teacher
        $this->assertSame([], $originals(['search' => 'zz-no-such-teacher']));

        $this->assertContains('Sutomo 9', Builder::availableCampuses());
        $this->assertNotContains('smp', Builder::availableCampuses());
        $this->assertArrayHasKey('smp', Builder::availableUnits());
    }

    public function test_page_renders_every_view_and_layout(): void
    {
        $this->leave('absA', 'approved', 'subA');
        $this->leave('absD', 'approved');
        $this->duty('subB', 'accepted', ['title' => 'Library duty']);
        $date = $this->mon->toDateString();

        $this->actingAs($this->principal);
        foreach ([
            "view=week&date={$date}",
            "view=week&layout=grid&date={$date}",
            "view=day&date={$date}",
            "view=day&layout=grid&date={$date}",
            "view=month&date={$date}",
            'view=day&date=' . $this->mon->subDay()->toDateString(), // Sunday
            'view=bogus&layout=bogus&date=not-a-date&show=bogus',
            "view=week&date={$date}&unit=smp&campus=Sutomo%201&q=zz",
        ] as $qs) {
            $this->get("/principal/substitution-timetable?{$qs}")->assertOk();
        }

        $this->get("/principal/substitution-timetable?view=week&date={$date}&campus=" . self::CAMPUS)
            ->assertOk()
            ->assertSee('No substitute')
            ->assertSee('ZZ subA')
            ->assertSee('Library duty')
            ->assertSee('Needs attention');
    }

    public function test_livewire_navigation_and_search(): void
    {
        $this->leave('absA', 'approved', 'subA');
        $this->actingAs($this->principal);
        Filament::setCurrentPanel(Filament::getPanel('principal'));

        Livewire::withQueryParams(['date' => $this->mon->toDateString()])
            ->test(Page::class)
            ->assertSet('activeView', 'week')
            ->call('nextWindow')->assertSet('focusDate', $this->mon->addWeek()->toDateString())
            ->call('prevWindow')
            ->call('setView', 'day')
            ->call('prevWindow')->assertSet('focusDate', $this->mon->subDays(2)->toDateString()) // skips Sunday
            ->call('nextWindow')->assertSet('focusDate', $this->mon->toDateString())
            ->call('setLayout', 'grid')->assertSet('display', 'grid')
            ->call('setView', 'month')->assertOk()
            ->call('openDay', $this->mon->toDateString())->assertSet('activeView', 'day')
            ->call('goTomorrow')->assertSet('activeView', 'day')
            ->set('focusDate', $this->mon->toDateString())
            ->set('activeView', 'week')
            ->set('search', 'absA')->assertSee('ZZ subA')
            ->set('search', 'nobody-matches-this')->assertDontSee('ZZ subA')
            ->call('resetFilters')->assertSet('search', null)->assertDontSee('Eligibility lookup')
            ->call('goToday')->assertSet('focusDate', CarbonImmutable::today()->toDateString());
    }

    public function test_rejecting_or_approving_closes_the_cover_search(): void
    {
        $leave = $this->leave('absA', 'pending');
        $leave->update(['auto_search_status' => 'open', 'auto_search_closes_at' => now()->addDay()]);
        $mk = fn (string $who, string $status) => SubstituteOffer::create([
            'teacher_leave_id' => $leave->id, 'teacher_id' => $this->t[$who]->id,
            'token' => SubstituteOffer::generateToken(), 'status' => $status,
            'sent_at' => now(), 'expires_at' => now()->addDay(), 'round' => 1,
        ]);
        $a = $mk('subA', 'interested');
        $b = $mk('subB', 'pending');

        SubstituteBroadcaster::settle($leave, $this->t['subA']->id);

        $this->assertSame('assigned', $a->fresh()->status);
        $this->assertSame('cancelled', $b->fresh()->status);
        $this->assertSame('closed_assigned', $leave->fresh()->auto_search_status);
    }

    public function test_offer_link_on_rejected_leave_is_closed(): void
    {
        $leave = $this->leave('absA', 'rejected');
        $offer = SubstituteOffer::create([
            'teacher_leave_id' => $leave->id, 'teacher_id' => $this->t['subA']->id,
            'token' => SubstituteOffer::generateToken(), 'status' => 'pending',
            'sent_at' => now(), 'expires_at' => now()->addDay(), 'round' => 1,
        ]);

        $url = URL::signedRoute('substitute-offer.accept', ['offer' => $offer->id, 'token' => $offer->token]);
        $this->get($url)->assertOk()->assertSee('withdrawn');
        $this->assertSame('pending', $offer->fresh()->status);
    }
}
