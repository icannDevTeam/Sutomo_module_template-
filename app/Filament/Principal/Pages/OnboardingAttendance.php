<?php

namespace App\Filament\Principal\Pages;

use App\Models\Application;
use App\Models\EnrollmentPeriod;
use App\Support\SchoolDirectory;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

/**
 * Cohort-level Onboarding Attendance.
 *
 * Per-period 5-day matrix view of every accepted student × observation day.
 * Inline cells let the Principal override teacher marks; bulk actions cover
 * "Mark all present today" for a class. Stage auto-advances when present>=5.
 */
class OnboardingAttendance extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Enrollment';
    protected static ?int $navigationSort = 5;
    protected static ?string $title = 'Onboarding Attendance';
    protected static ?string $navigationLabel = 'Onboarding Attendance';
    protected static ?string $slug = 'onboarding-attendance';
    protected static string $view = 'filament.principal.pages.onboarding-attendance';

    public ?int $periodId = null;
    public ?string $classFilter = null;
    public ?string $search = null;

    public function mount(): void
    {
        $this->periodId = EnrollmentPeriod::query()
            ->whereNotNull('observation_start_date')
            ->orderByDesc('opens_at')->value('id')
            ?? EnrollmentPeriod::query()->orderByDesc('opens_at')->value('id');
    }

    /* -------------------------------------------------------------
       VIEW DATA
       ------------------------------------------------------------- */
    protected function getViewData(): array
    {
        $periods = EnrollmentPeriod::query()->orderByDesc('opens_at')->get();
        $period = $periods->firstWhere('id', $this->periodId) ?? $periods->first();

        $dates = $period ? $this->buildDates($period) : [];
        $apps  = $this->loadCohort($period);

        // Class filter dropdown values
        $classes = $apps->pluck('class_label')->filter()->unique()->sort()->values();

        if ($this->classFilter) {
            $apps = $apps->where('class_label', $this->classFilter)->values();
        }
        if ($this->search) {
            $s = mb_strtolower($this->search);
            $apps = $apps->filter(fn ($a) => str_contains(mb_strtolower($a->name), $s)
                || str_contains(mb_strtolower($a->code ?? ''), $s))->values();
        }

        // Footer column tallies (present count per day across cohort).
        $colTotals = array_fill_keys($dates, ['present' => 0, 'absent' => 0, 'pending' => 0]);
        foreach ($apps as $a) {
            $days = data_get($a->meta, 'observation.days', []);
            foreach ($dates as $d) {
                $p = $days[$d]['present'] ?? null;
                if ($p === true) $colTotals[$d]['present']++;
                elseif ($p === false) $colTotals[$d]['absent']++;
                else $colTotals[$d]['pending']++;
            }
        }

        return [
            'periods'   => $periods,
            'period'    => $period,
            'dates'     => $dates,
            'apps'      => $apps,
            'classes'   => $classes,
            'colTotals' => $colTotals,
        ];
    }

    /** Build the 5-day window from the period (skipping weekends). */
    public function buildDates(EnrollmentPeriod $period): array
    {
        if (! $period->observation_start_date) return [];
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

    /** Cohort = onboarding-stage applications in the period; adds class_label virtual. */
    protected function loadCohort(?EnrollmentPeriod $period)
    {
        if (! $period) return collect();
        return Application::query()
            ->where('enrollment_period_id', $period->id)
            ->whereIn('status', ['accepted','dev_fee','books','class_assigned','observing','id_issued','tuition','activated'])
            ->orderBy('name')
            ->get()
            ->each(function ($a) {
                $a->class_label = data_get($a->meta, 'class.label') ?? '—';
            });
    }

    /* -------------------------------------------------------------
       ACTIONS
       ------------------------------------------------------------- */

    /** Toggle a single cell present → absent → pending → present. */
    public function cycleCell(int $appId, string $date): void
    {
        $a = Application::find($appId);
        if (! $a) return;
        $meta = $a->meta ?? [];
        $days = data_get($meta, 'observation.days', []);
        if (! isset($days[$date])) return;
        $current = $days[$date]['present'] ?? null;
        $next = match ($current) {
            true  => false,
            false => null,
            default => true,
        };
        $days[$date] = [
            'present' => $next,
            'by'      => $next === null ? null : 'Principal override',
            'at'      => $next === null ? null : now()->toIso8601String(),
        ];
        $meta['observation']['days'] = $days;
        $a->meta = $meta;
        $a->save();
    }

    public function markAllPresentTodayAction(): Action
    {
        return Action::make('markAllPresentToday')
            ->label("Mark all present today")
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalDescription("Mark every visible student as Present for today's observation day. Useful for a low-friction principal sweep at the end of the day.")
            ->action(function () {
                $period = EnrollmentPeriod::find($this->periodId);
                if (! $period) return;
                $today = now()->toDateString();
                $dates = $this->buildDates($period);
                if (! in_array($today, $dates, true)) {
                    Notification::make()
                        ->title("Today (".now()->translatedFormat('D, d M').") isn't in the observation window")
                        ->body("Weekends are skipped automatically. The window runs ".
                            \Illuminate\Support\Carbon::parse($dates[0] ?? $period->observation_start_date)->translatedFormat('d M').
                            " → ".\Illuminate\Support\Carbon::parse(end($dates) ?: $period->observation_start_date)->translatedFormat('d M').".")
                        ->warning()->send();
                    return;
                }
                $apps = $this->loadCohort($period);
                if ($this->classFilter) {
                    $apps = $apps->where('class_label', $this->classFilter)->values();
                }
                if ($this->search) {
                    $s = mb_strtolower($this->search);
                    $apps = $apps->filter(fn ($a) => str_contains(mb_strtolower($a->name), $s)
                        || str_contains(mb_strtolower($a->code ?? ''), $s))->values();
                }
                $n = 0;
                foreach ($apps as $a) {
                    $meta = $a->meta ?? [];
                    $days = data_get($meta, 'observation.days', []);
                    if (! isset($days[$today])) continue;
                    $days[$today] = ['present' => true, 'by' => 'Principal override', 'at' => now()->toIso8601String()];
                    $meta['observation']['days'] = $days;
                    $a->meta = $meta;
                    $a->save();
                    $n++;
                }
                Notification::make()->title("Marked {$n} student(s) present")->body("Date: ".now()->translatedFormat('D, d M Y'))->success()->send();
            });
    }

    public function setActivePeriod(int $id): void
    {
        $this->periodId = $id;
        // Reset filters — class lists & search context vary between periods.
        $this->classFilter = null;
        $this->search = null;
    }

    public function setClassFilter(?string $class): void
    {
        $this->classFilter = $class ?: null;
    }

    /** Helper for the blade view. */
    public static function unitLabel(?string $slug): string
    {
        return SchoolDirectory::unitLabel($slug) ?? '—';
    }

    public static function campusLabel(?string $code): string
    {
        return SchoolDirectory::campusLabel($code) ?? ($code ?? '—');
    }
}
