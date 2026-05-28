<?php

namespace App\Filament\Principal\Pages;

use App\Models\Application;
use App\Models\EnrollmentPeriod;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;

/**
 * Enrollment Overview — supervisor dashboard for the active intake.
 *
 * Shows the high-level KPIs for the onboarding window plus two side-by-side
 * panels for Attendance and Placement Exam Scores of the selected day.
 */
class EnrollmentOverview extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';
    protected static ?string $navigationGroup = 'Enrollment';
    protected static ?int $navigationSort = 0;
    protected static ?string $title = 'Enrollment Overview';
    protected static ?string $navigationLabel = 'Enrollment Overview';
    protected static ?string $slug = 'enrollment-overview';
    protected static string $view = 'filament.principal.pages.enrollment-overview';

    public ?int $periodId = null;

    #[Url(as: 'day')]
    public int $dayIndex = 0;

    #[Url(as: 'q1')]
    public string $attendanceSearch = '';

    #[Url(as: 'f1')]
    public string $attendanceFilter = 'all'; // all|present|absent

    #[Url(as: 'q2')]
    public string $scoreSearch = '';

    #[Url(as: 'f2')]
    public string $scoreFilter = 'all'; // all|submitted|pending|absent

    public int $attendancePage = 1;
    public int $scoresPage = 1;

    public const PAGE_SIZE = 5;

    public static function getNavigationBadge(): ?string
    {
        return 'New';
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public function mount(): void
    {
        $this->periodId = EnrollmentPeriod::query()
            ->whereNotNull('observation_start_date')
            ->orderByDesc('opens_at')->value('id')
            ?? EnrollmentPeriod::query()->orderByDesc('opens_at')->value('id');
    }

    public function setDay(int $index): void
    {
        $this->dayIndex = max(0, $index);
        $this->attendancePage = 1;
        $this->scoresPage = 1;
    }

    public function setAttendancePage(int $page): void
    {
        $this->attendancePage = max(1, $page);
    }

    public function setScoresPage(int $page): void
    {
        $this->scoresPage = max(1, $page);
    }

    protected function getViewData(): array
    {
        $period = $this->periodId
            ? EnrollmentPeriod::find($this->periodId)
            : null;

        $dates  = $period ? $this->buildDates($period) : [];
        $dayKey = $dates[$this->dayIndex] ?? ($dates[0] ?? null);

        $cohort = $this->loadCohort($period);
        $totalApplicants = $cohort->count();

        // Per-applicant attendance status for the selected day
        $attendance = $cohort->map(function ($a) use ($dayKey) {
            $present = $dayKey ? data_get($a->meta, "observation.days.$dayKey.present") : null;
            return (object) [
                'id'      => $a->id,
                'name'    => $a->name,
                'code'    => $a->code,
                'initials'=> self::initialsOf($a->name),
                'status'  => $present === true ? 'present' : ($present === false ? 'absent' : 'pending'),
            ];
        });

        $presentToday = $attendance->where('status', 'present')->count();
        $absentToday  = $attendance->where('status', 'absent')->count();

        // Per-applicant placement-exam row
        $scores = $cohort->map(function ($a) use ($attendance) {
            $att = $attendance->firstWhere('id', $a->id);
            $hasScore = ! is_null($a->placement_score);
            $isAbsent = $att && $att->status === 'absent';
            $status = $hasScore ? 'submitted' : ($isAbsent ? 'absent' : 'pending');
            return (object) [
                'id'       => $a->id,
                'name'     => $a->name,
                'initials' => self::initialsOf($a->name),
                'score'    => $hasScore ? number_format((float) $a->placement_score, 1) : null,
                'status'   => $status,
                'sent'     => (bool) data_get($a->meta, 'principal.score_sent_at') || $hasScore,
            ];
        });

        $scoresSubmitted = $scores->where('status', 'submitted')->count();
        $scoresPending   = $scores->where('status', 'pending')->count();
        $sentToPrincipal = $scores->where('sent', true)->where('status', 'submitted')->count();

        // Filter & paginate
        $attFiltered = $attendance
            ->when($this->attendanceFilter !== 'all', fn ($c) => $c->where('status', $this->attendanceFilter))
            ->when($this->attendanceSearch !== '', function ($c) {
                $s = mb_strtolower($this->attendanceSearch);
                return $c->filter(fn ($r) => str_contains(mb_strtolower($r->name), $s));
            })
            ->values();

        $scoreFiltered = $scores
            ->when($this->scoreFilter !== 'all', fn ($c) => $c->where('status', $this->scoreFilter))
            ->when($this->scoreSearch !== '', function ($c) {
                $s = mb_strtolower($this->scoreSearch);
                return $c->filter(fn ($r) => str_contains(mb_strtolower($r->name), $s));
            })
            ->values();

        $attTotal   = $attFiltered->count();
        $scoreTotal = $scoreFiltered->count();
        $attRows    = $attFiltered->forPage($this->attendancePage, self::PAGE_SIZE)->values();
        $scoreRows  = $scoreFiltered->forPage($this->scoresPage, self::PAGE_SIZE)->values();

        // Day label + window
        $dayDate = $dayKey ? Carbon::parse($dayKey) : null;
        $dayLabel = 'Day ' . ($this->dayIndex + 1);
        $dayHuman = $dayDate?->format('D, M j, Y');
        $window = $period
            ? sprintf('%02d:00–%02d:00',
                (int) Carbon::parse($period->exam_starts_at ?? '08:00')->format('H'),
                (int) Carbon::parse($period->exam_starts_at ?? '08:00')->format('H') + 4)
            : '08:00–12:00';

        // Period header pieces
        $periodWindow = null;
        if ($period && $period->observation_start_date) {
            $start = Carbon::parse($period->observation_start_date);
            $end   = $start->copy()->addDays(count($dates) - 1);
            $periodWindow = $start->format('M j') . '–' . $end->format('j, Y');
        }
        $room = $period?->exam_venue ?? 'Room 201 & 202';
        $ay = $period?->academic_year;
        if (! $ay && $period?->name) {
            if (preg_match('/(\d{4}\/\d{4})/', $period->name, $m)) {
                $ay = $m[1];
            }
        }
        $ay = $ay ?: '2025/2026';

        return [
            'period'           => $period,
            'dates'            => $dates,
            'dayIndex'         => $this->dayIndex,
            'dayKey'           => $dayKey,
            'dayLabel'         => $dayLabel,
            'dayHuman'         => $dayHuman,
            'dayWindow'        => $window,
            'periodWindow'     => $periodWindow,
            'room'             => $room,
            'academicYear'     => $ay,

            'totalApplicants'  => $totalApplicants,
            'presentToday'     => $presentToday,
            'absentToday'      => $absentToday,
            'scoresSubmitted'  => $scoresSubmitted,
            'scoresPending'    => $scoresPending,
            'sentToPrincipal'  => $sentToPrincipal,

            'attRows'          => $attRows,
            'attTotal'         => $attTotal,
            'attPage'          => $this->attendancePage,
            'attLastPage'      => max(1, (int) ceil($attTotal / self::PAGE_SIZE)),
            'attFrom'          => $attTotal ? (($this->attendancePage - 1) * self::PAGE_SIZE) + 1 : 0,
            'attTo'            => min($attTotal, $this->attendancePage * self::PAGE_SIZE),

            'scoreRows'        => $scoreRows,
            'scoreTotal'       => $scoreTotal,
            'scorePage'        => $this->scoresPage,
            'scoreLastPage'    => max(1, (int) ceil($scoreTotal / self::PAGE_SIZE)),
            'scoreFrom'        => $scoreTotal ? (($this->scoresPage - 1) * self::PAGE_SIZE) + 1 : 0,
            'scoreTo'          => min($scoreTotal, $this->scoresPage * self::PAGE_SIZE),
        ];
    }

    /** Build the 5-day onboarding window from the period (skips weekends). */
    public function buildDates(EnrollmentPeriod $period): array
    {
        if (! $period->observation_start_date) {
            return [];
        }
        $days = max(3, (int) ($period->observation_days ?? 5));
        $out = [];
        $cursor = Carbon::parse($period->observation_start_date);
        for ($i = 0; $i < $days; $i++) {
            while ($cursor->isWeekend()) $cursor->addDay();
            $out[] = $cursor->toDateString();
            $cursor->addDay();
        }
        return $out;
    }

    protected function loadCohort(?EnrollmentPeriod $period)
    {
        if (! $period) return collect();
        return Application::query()
            ->where('enrollment_period_id', $period->id)
            ->whereIn('status', Application::ONBOARDING_STATUSES)
            ->orderBy('name')
            ->get();
    }

    protected static function initialsOf(?string $name): string
    {
        if (! $name) return '?';
        $parts = preg_split('/\s+/', trim($name));
        $first = mb_substr($parts[0] ?? '', 0, 1);
        $second = isset($parts[1]) ? mb_substr($parts[1], 0, 1) : '';
        return mb_strtoupper($first . $second);
    }

    /** Build a compact paginator window like [1, 2, '…', N]. */
    public function pageWindow(int $current, int $last): array
    {
        if ($last <= 5) return range(1, max(1, $last));
        $out = [1, 2];
        if ($current > 3) $out[] = '…';
        if ($current > 2 && $current < $last - 1) $out[] = $current;
        if ($current < $last - 2) $out[] = '…';
        $out[] = $last;
        return $out;
    }
}
