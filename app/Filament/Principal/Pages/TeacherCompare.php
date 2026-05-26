<?php

namespace App\Filament\Principal\Pages;

use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\TeacherCertification;
use App\Models\TeacherDocument;
use App\Models\TeacherObservation;
use App\Models\TeacherTraining;
use App\Models\DutyAssignment;
use App\Support\CsvExporter;
use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeacherCompare extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationGroup = 'Teachers';
    protected static ?string $title = 'Compare Teachers';
    protected static ?int $navigationSort = 7;
    protected static string $view = 'filament.principal.pages.teacher-compare';

    public ?array $data = ['ids' => []];

    public string $activeTab = 'compare'; // 'compare' | 'leaderboard'

    public ?string $lbCampus = null;
    public ?string $lbDept = null;
    public ?string $lbStatus = null;

    /** @var array<int,string> */
    public array $lbCriteria = [
        'attendance',
        'observation_avg',
        'certifications',
        'cpd_hours',
        'initiatives',
        'punctuality',
        'voluntary_requests',
        'documents_complete',
    ];

    public const CRITERIA_LABELS = [
        'attendance'         => 'Attendance',
        'observation_avg'    => 'Observation Avg',
        'certifications'     => 'Certifications',
        'cpd_hours'          => 'CPD Hours',
        'initiatives'        => 'Initiatives',
        'punctuality'        => 'Punctuality',
        'voluntary_requests' => 'Voluntary Requests',
        'documents_complete' => 'Documents Complete',
    ];

    public const ALL_CRITERIA = [
        'attendance',
        'observation_avg',
        'certifications',
        'cpd_hours',
        'initiatives',
        'punctuality',
        'voluntary_requests',
        'documents_complete',
    ];

    public function mount(): void
    {
        $ids = request()->query('ids');
        if (is_string($ids)) {
            $this->data['ids'] = collect(explode(',', $ids))->filter()->map('intval')->take(3)->all();
        }
        $this->form->fill(['ids' => $this->data['ids']]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('ids')
                    ->label('Pick up to 3 teachers')
                    ->multiple()
                    ->maxItems(3)
                    ->options(Teacher::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->live(),
            ])
            ->statePath('data');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['compare', 'leaderboard'], true) ? $tab : 'compare';
    }

    public function toggleCriterion(string $key): void
    {
        if (! in_array($key, self::ALL_CRITERIA, true)) {
            return;
        }
        if (in_array($key, $this->lbCriteria, true)) {
            $this->lbCriteria = array_values(array_diff($this->lbCriteria, [$key]));
        } else {
            $this->lbCriteria[] = $key;
        }
    }

    public function getViewData(): array
    {
        $data = [
            'cards'          => $this->buildCompareCards(),
            'activeTab'      => $this->activeTab,
            'criteriaLabels' => self::CRITERIA_LABELS,
            'allCriteria'    => self::ALL_CRITERIA,
            'leaderboard'    => collect(),
            'campusOptions'  => [],
            'deptOptions'    => [],
            'statusOptions'  => [],
        ];

        if ($this->activeTab === 'leaderboard') {
            $data['leaderboard']   = $this->buildLeaderboard();
            $data['campusOptions'] = $this->safePluck('campus');
            $data['deptOptions']   = $this->safePluck('dept');
            $data['statusOptions'] = Teacher::STATUSES;
        }

        return $data;
    }

    protected function safePluck(string $column): array
    {
        try {
            return Teacher::query()
                ->whereNotNull($column)
                ->distinct()
                ->orderBy($column)
                ->pluck($column, $column)
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected function buildCompareCards()
    {
        $ids = $this->data['ids'] ?? [];
        if (empty($ids)) {
            return collect();
        }
        try {
            $teachers = Teacher::with(['homeroomClasses', 'mentor'])->whereIn('id', $ids)->get();
        } catch (\Throwable $e) {
            return collect();
        }

        return $teachers->map(function ($t) {
            $start = now()->startOfMonth();
            try {
                $rows = TeacherAttendance::where('teacher_id', $t->id)->where('date', '>=', $start)->get();
                $p = $rows->where('status', 'present')->count();
                $base = $p + $rows->where('status', 'late')->count() + $rows->where('status', 'absent')->count();
                $attendance = $base ? round(($p / $base) * 100) : null;
            } catch (\Throwable $e) {
                $attendance = null;
            }

            return [
                'teacher'        => $t,
                'attendance'     => $attendance,
                'cert_count'     => $this->safeCount(fn () => $t->formalCertifications()->count()),
                'obs_count'      => $this->safeCount(fn () => $t->observations()->count()),
                'homeroom_count' => $this->safeCount(fn () => $t->homeroomClasses()->count()),
            ];
        });
    }

    protected function safeCount(\Closure $fn): int
    {
        try {
            return (int) $fn();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function buildLeaderboard(): \Illuminate\Support\Collection
    {
        try {
            $q = Teacher::query();
            if ($this->lbCampus) $q->where('campus', $this->lbCampus);
            if ($this->lbDept)   $q->where('dept', $this->lbDept);
            if ($this->lbStatus) $q->where('status', $this->lbStatus);
            $teachers = $q->orderBy('name')->get();
        } catch (\Throwable $e) {
            return collect();
        }

        $criteria = empty($this->lbCriteria) ? self::ALL_CRITERIA : $this->lbCriteria;

        $rows = $teachers->map(function (Teacher $t) use ($criteria) {
            $scores = [];
            foreach (self::ALL_CRITERIA as $key) {
                $scores[$key] = $this->computeMetric($t, $key);
            }
            $selected = array_intersect_key($scores, array_flip($criteria));
            $total = empty($selected) ? 0 : (int) round(array_sum($selected) / count($selected));
            return [
                'teacher' => $t,
                'scores'  => $scores,
                'total'   => $total,
                'badge'   => null,
                'rank'    => 0,
            ];
        })->sortByDesc('total')->values();

        $n = $rows->count();
        if ($n === 0) {
            return $rows;
        }
        $decileThreshold = (int) max(1, ceil($n / 10));
        return $rows->map(function ($r, $i) use ($n, $decileThreshold) {
            $r['rank'] = $i + 1;
            if ($i < 3) {
                $r['badge'] = 'top';
            } elseif ($i >= ($n - $decileThreshold)) {
                $r['badge'] = 'support';
            }
            return $r;
        });
    }

    protected function computeMetric(Teacher $t, string $key): int
    {
        try {
            return match ($key) {
                'attendance'         => $this->metricAttendance($t),
                'observation_avg'    => $this->metricObservationAvg($t),
                'certifications'     => $this->metricCertifications($t),
                'cpd_hours'          => $this->metricCpdHours($t),
                'initiatives'        => $this->metricInitiatives($t),
                'punctuality'        => $this->metricPunctuality($t),
                'voluntary_requests' => $this->metricVoluntaryRequests($t),
                'documents_complete' => $this->metricDocumentsComplete($t),
                default              => 0,
            };
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function metricAttendance(Teacher $t): int
    {
        try {
            $start = now()->subDays(30);
            $rows = TeacherAttendance::where('teacher_id', $t->id)->where('date', '>=', $start)->get();
            $p = $rows->where('status', 'present')->count();
            $base = $p + $rows->where('status', 'late')->count() + $rows->where('status', 'absent')->count();
            if (! $base) return 0;
            return (int) round(($p / $base) * 100);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function metricObservationAvg(Teacher $t): int
    {
        try {
            $obs = TeacherObservation::where('teacher_id', $t->id)->get();
            if ($obs->isEmpty()) return 0;
            $vals = $obs->map(fn ($o) => (float) ($o->average_score ?? 0))->filter(fn ($v) => $v > 0);
            if ($vals->isEmpty()) return 0;
            $avg = $vals->avg();
            return (int) min(100, round($avg * 20));
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function metricCertifications(Teacher $t): int
    {
        try {
            $n = TeacherCertification::where('teacher_id', $t->id)->count();
            return (int) min(100, $n * 10);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function metricCpdHours(Teacher $t): int
    {
        try {
            $hasHours = Schema::hasColumn('teacher_trainings', 'hours');
            $hasCertified = Schema::hasColumn('teacher_trainings', 'hours_certified');
            $hours = 0.0;
            if ($hasHours) {
                $hours = (float) TeacherTraining::where('teacher_id', $t->id)->sum('hours');
            }
            if ($hours <= 0 && $hasCertified) {
                $hours = (float) TeacherTraining::where('teacher_id', $t->id)->sum('hours_certified');
            }
            return (int) min(100, (int) round($hours));
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function metricInitiatives(Teacher $t): int
    {
        try {
            $n = is_array($t->initiatives) ? count($t->initiatives) : 0;
            return (int) min(100, $n * 25);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function metricPunctuality(Teacher $t): int
    {
        try {
            if (! Schema::hasTable('teacher_attendance')) return 80;
            $lateCount = TeacherAttendance::where('teacher_id', $t->id)
                ->where('date', '>=', now()->subDays(30))
                ->where('status', 'late')
                ->count();
            if ($lateCount === 0) return 80;
            return (int) max(0, 100 - ($lateCount * 10));
        } catch (\Throwable $e) {
            return 80;
        }
    }

    protected function metricVoluntaryRequests(Teacher $t): int
    {
        try {
            if (! Schema::hasColumn('duty_assignments', 'voluntary')) {
                return 0;
            }
            $n = DutyAssignment::where('teacher_id', $t->id)
                ->where('voluntary', true)
                ->count();
            return (int) min(100, $n * 20);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function metricDocumentsComplete(Teacher $t): int
    {
        try {
            $total = TeacherDocument::where('teacher_id', $t->id)->count();
            if ($total === 0) return 75;
            $verified = TeacherDocument::where('teacher_id', $t->id)
                ->where('status', 'verified')
                ->count();
            return (int) round(($verified / $total) * 100);
        } catch (\Throwable $e) {
            return 75;
        }
    }

    public function exportLeaderboard(): StreamedResponse
    {
        $rows = $this->buildLeaderboard();
        $criteria = empty($this->lbCriteria) ? self::ALL_CRITERIA : $this->lbCriteria;

        $columns = [
            'Rank'   => fn ($r) => $r['rank'],
            'Name'   => fn ($r) => $r['teacher']->name,
            'Campus' => fn ($r) => $r['teacher']->campus,
            'Dept'   => fn ($r) => $r['teacher']->dept,
            'Status' => fn ($r) => $r['teacher']->status,
        ];
        foreach ($criteria as $key) {
            $label = self::CRITERIA_LABELS[$key] ?? $key;
            $columns[$label] = fn ($r) => $r['scores'][$key] ?? 0;
        }
        $columns['Total'] = fn ($r) => $r['total'];
        $columns['Badge'] = fn ($r) => $r['badge'];

        return CsvExporter::download(
            $rows,
            $columns,
            CsvExporter::filename('teacher-leaderboard'),
        );
    }
}
