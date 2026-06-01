<?php

namespace App\Filament\Principal\Pages;

use App\Models\Teacher;
use App\Models\TeacherLeave;
use App\Support\SubstituteBroadcaster;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;

class SubmitLeaveOnBehalf extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-date-range';
    protected static ?string $navigationGroup = 'Approvals';
    protected static ?int $navigationSort = 1;
    protected static ?string $title = 'Submit Leave on Behalf';
    protected static ?string $navigationLabel = 'Submit Leave for Teacher';
    protected static ?string $slug = 'submit-leave';
    protected static string $view = 'filament.principal.pages.submit-leave-on-behalf';

    #[Url(as: 't')]
    public ?int $teacherId = null;

    #[Url(as: 'm')]
    public ?string $monthKey = null;  // YYYY-MM

    public string $type = 'sick';
    public ?string $startsAt = null;
    public ?string $endsAt = null;
    public ?string $reason = null;
    public bool $autoSearchEnabled = true;

    public function mount(): void
    {
        $this->monthKey = $this->monthKey ?: now()->format('Y-m');
    }

    public function getViewData(): array
    {
        $teachers = Teacher::query()
            ->whereNotIn('status', ['alumni'])
            ->orderBy('name')
            ->get(['id', 'name', 'subject', 'campus', 'annual_quota', 'sick_quota', 'personal_quota']);

        $teacher = $this->teacherId ? $teachers->firstWhere('id', $this->teacherId) : null;

        $month = Carbon::createFromFormat('Y-m', $this->monthKey)->startOfMonth();
        $prev = $month->copy()->subMonth()->format('Y-m');
        $next = $month->copy()->addMonth()->format('Y-m');

        // Build calendar grid
        $firstDow = (int) $month->copy()->startOfMonth()->dayOfWeekIso; // 1=Mon..7=Sun
        $daysIn   = $month->daysInMonth;
        $cells = [];
        for ($i = 1; $i < $firstDow; $i++) $cells[] = null;
        for ($d = 1; $d <= $daysIn; $d++) {
            $cells[] = $month->copy()->day($d);
        }
        while (count($cells) % 7 !== 0) $cells[] = null;

        // Existing leaves for this teacher
        $leaves = collect();
        if ($teacher) {
            $leaves = TeacherLeave::where('teacher_id', $teacher->id)
                ->whereIn('status', ['approved', 'pending'])
                ->get();
        }

        $leavesByDate = [];
        foreach ($leaves as $l) {
            if (! $l->starts_at || ! $l->ends_at) continue;
            $cursor = $l->starts_at->copy();
            while ($cursor->lte($l->ends_at)) {
                $leavesByDate[$cursor->toDateString()][] = $l;
                $cursor->addDay();
            }
        }

        // Quota cards
        $quotas = [];
        if ($teacher) {
            foreach (['sick' => 'Sick', 'emergency' => 'Personal/Emergency', 'sabbatical' => 'Annual/Sabbatical'] as $key => $label) {
                $usedKey = $key;
                $limit = $teacher->quotaFor($key);
                $used  = $teacher->leaveDaysUsed($key, $month->year);
                $quotas[] = [
                    'key'   => $key,
                    'label' => $label,
                    'used'  => $used,
                    'limit' => $limit,
                    'remaining' => max(0, $limit - $used),
                    'pct'   => $limit > 0 ? min(100, ($used / $limit) * 100) : 0,
                    'color' => $used >= $limit ? 'danger' : ($used / max(1,$limit) >= 0.75 ? 'warning' : 'success'),
                ];
            }
        }

        // Selected day count (excluding weekends)
        $workingDays = 0;
        if ($this->startsAt && $this->endsAt) {
            try {
                $s = Carbon::parse($this->startsAt);
                $e = Carbon::parse($this->endsAt);
                if ($e->gte($s)) {
                    $c = $s->copy();
                    while ($c->lte($e)) {
                        if (! in_array($c->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
                            $workingDays++;
                        }
                        $c->addDay();
                    }
                }
            } catch (\Throwable $e) {}
        }

        return [
            'teachers'      => $teachers,
            'teacher'       => $teacher,
            'month'         => $month,
            'monthLabel'    => $month->format('F Y'),
            'prevMonth'     => $prev,
            'nextMonth'     => $next,
            'cells'         => $cells,
            'leavesByDate'  => $leavesByDate,
            'quotas'        => $quotas,
            'workingDays'   => $workingDays,
            'types'         => TeacherLeave::TYPES,
        ];
    }

    public function pickDate(string $date): void
    {
        if (! $this->teacherId) return;

        // First click: set start. Second click: set end (or reset if before start).
        if (! $this->startsAt || ($this->startsAt && $this->endsAt)) {
            $this->startsAt = $date;
            $this->endsAt = $date;
            return;
        }

        if (Carbon::parse($date)->lt(Carbon::parse($this->startsAt))) {
            $this->startsAt = $date;
            $this->endsAt = $date;
            return;
        }

        $this->endsAt = $date;
    }

    public function clearSelection(): void
    {
        $this->startsAt = null;
        $this->endsAt = null;
    }

    public function submit(): void
    {
        if (! $this->teacherId || ! $this->startsAt || ! $this->endsAt) {
            Notification::make()->title('Pick a teacher and a date range first.')->danger()->send();
            return;
        }

        $teacher = Teacher::find($this->teacherId);
        if (! $teacher) return;

        $leave = TeacherLeave::create([
            'teacher_id'          => $this->teacherId,
            'type'                => $this->type,
            'starts_at'           => $this->startsAt,
            'ends_at'             => $this->endsAt,
            'reason'              => $this->reason ?: 'Filed by principal on behalf of teacher.',
            'status'              => 'pending',
            'auto_search_enabled' => $this->autoSearchEnabled,
        ]);

        $invited = 0;
        if ($this->autoSearchEnabled) {
            $offers = SubstituteBroadcaster::startAutoSearch($leave);
            $invited = count($offers);
        }

        Notification::make()
            ->title('Leave submitted for ' . $teacher->name)
            ->body($invited
                ? "Auto substitute search started. {$invited} teacher(s) invited."
                : 'Auto substitute search disabled — assign manually.')
            ->success()
            ->send();

        $this->reset(['startsAt', 'endsAt', 'reason']);
    }
}
