<?php

namespace App\Filament\Principal\Resources\TeacherResource\Pages;

use App\Filament\Principal\Resources\TeacherResource;
use App\Models\Teacher;
use App\Models\TeacherReviewSavedView;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Livewire\Attributes\Url;

class ListTeachers extends Page
{
    protected static string $resource = TeacherResource::class;
    protected static string $view = 'filament.principal.teacher.review-grid';

    public function getTitle(): string
    {
        return 'Teacher Review';
    }

    // ===== Filters (URL-synced) =====
    #[Url(as: 'q')]      public string $search = '';
    #[Url] public string $campus = '';
    #[Url] public string $dept = '';
    #[Url] public string $subject = '';
    #[Url] public string $status = '';
    #[Url] public string $employment = '';
    #[Url] public string $gender = '';
    #[Url] public string $readiness = '';
    #[Url] public string $tenureBand = '';
    #[Url] public string $attendanceBand = '';
    #[Url] public string $obsBand = '';
    #[Url] public bool $dueOnly = false;
    #[Url] public bool $openQueriesOnly = false;
    #[Url] public bool $pinnedNotesOnly = false;
    #[Url] public string $onLeaveWindow = ''; // '' | today | week
    #[Url] public bool $contractEnding90 = false;
    #[Url] public bool $missingDocsOnly = false;
    #[Url] public ?int $savedViewId = null;
    #[Url] public string $sort = 'name';

    public const FILTER_PROPS = [
        'search', 'campus', 'dept', 'subject', 'status', 'employment', 'gender',
        'readiness', 'tenureBand', 'attendanceBand', 'obsBand',
        'dueOnly', 'openQueriesOnly', 'pinnedNotesOnly', 'onLeaveWindow',
        'contractEnding90', 'missingDocsOnly', 'sort',
    ];

    public function mount(): void
    {
        if ($this->isUrlClean() && auth()->check()) {
            $default = TeacherReviewSavedView::where('user_id', auth()->id())
                ->where('is_default', true)->first();
            if ($default) {
                $this->applyView($default);
            }
        }
    }

    protected function isUrlClean(): bool
    {
        foreach (self::FILTER_PROPS as $p) {
            $v = $this->{$p};
            if (is_bool($v)) {
                if ($v) return false;
            } else {
                if ($p === 'sort' && $v === 'name') continue;
                if ((string) $v !== '') return false;
            }
        }
        return $this->savedViewId === null;
    }

    protected function applyView(TeacherReviewSavedView $v): void
    {
        $f = $v->filters ?? [];
        foreach (self::FILTER_PROPS as $p) {
            if (array_key_exists($p, $f)) {
                $this->{$p} = $f[$p];
            }
        }
        $this->savedViewId = $v->id;
    }

    public function selectSavedView(?int $id): void
    {
        if (! $id) {
            $this->resetFilters();
            return;
        }
        $v = TeacherReviewSavedView::where('user_id', auth()->id())->find($id);
        if ($v) $this->applyView($v);
    }

    public function deleteSavedView(int $id): void
    {
        $v = TeacherReviewSavedView::where('user_id', auth()->id())->find($id);
        if (! $v) return;
        $v->delete();
        if ($this->savedViewId === $id) $this->savedViewId = null;
        Notification::make()->title('View deleted')->success()->send();
    }

    public function setDefaultSavedView(int $id): void
    {
        $v = TeacherReviewSavedView::where('user_id', auth()->id())->find($id);
        if (! $v) return;
        TeacherReviewSavedView::where('user_id', auth()->id())->update(['is_default' => false]);
        $v->update(['is_default' => true]);
        Notification::make()->title('Default view set')->success()->send();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->campus = $this->dept = $this->subject = $this->status = '';
        $this->employment = $this->gender = $this->readiness = '';
        $this->tenureBand = $this->attendanceBand = $this->obsBand = '';
        $this->dueOnly = $this->openQueriesOnly = $this->pinnedNotesOnly = false;
        $this->onLeaveWindow = '';
        $this->contractEnding90 = $this->missingDocsOnly = false;
        $this->savedViewId = null;
        $this->sort = 'name';
    }

    public function clearFilter(string $name): void
    {
        if (! property_exists($this, $name)) return;
        $current = $this->{$name};
        $this->{$name} = is_bool($current) ? false : '';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('saveView')
                ->label('Save current view')
                ->icon('heroicon-o-bookmark-square')
                ->color('gray')
                ->form([
                    Forms\Components\TextInput::make('name')->required()->maxLength(100),
                    Forms\Components\Toggle::make('is_default')->label('Set as my default'),
                ])
                ->action(function (array $data) {
                    if (! auth()->check()) return;
                    if (! empty($data['is_default'])) {
                        TeacherReviewSavedView::where('user_id', auth()->id())
                            ->update(['is_default' => false]);
                    }
                    $v = TeacherReviewSavedView::create([
                        'user_id'    => auth()->id(),
                        'name'       => $data['name'],
                        'filters'    => $this->currentFilters(),
                        'is_default' => (bool) ($data['is_default'] ?? false),
                    ]);
                    $this->savedViewId = $v->id;
                    Notification::make()->title('View saved')->success()->send();
                }),
        ];
    }

    public function currentFilters(): array
    {
        $out = [];
        foreach (self::FILTER_PROPS as $p) $out[$p] = $this->{$p};
        return $out;
    }

    protected function buildRows(): array
    {
        $q = Teacher::query()->with(['promotionSetter']);

        if ($this->search !== '') {
            $s = '%' . trim($this->search) . '%';
            $q->where(function ($w) use ($s) {
                $w->where('name', 'like', $s)
                  ->orWhere('code', 'like', $s)
                  ->orWhere('employee_no', 'like', $s)
                  ->orWhere('subject', 'like', $s);
            });
        }
        foreach (['campus','dept','subject','status','employment','gender'] as $col) {
            if ($this->{$col} !== '') $q->where($col, $this->{$col});
        }

        if ($this->tenureBand === 'lt1') {
            $q->where('joined_at', '>', now()->subYear());
        } elseif ($this->tenureBand === '1to3') {
            $q->whereBetween('joined_at', [now()->subYears(3), now()->subYear()]);
        } elseif ($this->tenureBand === '3to7') {
            $q->whereBetween('joined_at', [now()->subYears(7), now()->subYears(3)]);
        } elseif ($this->tenureBand === 'gt7') {
            $q->where('joined_at', '<=', now()->subYears(7));
        }

        if ($this->contractEnding90) {
            $q->whereNotNull('contract_end')
              ->whereBetween('contract_end', [now()->startOfDay(), now()->addDays(90)]);
        }

        if ($this->dueOnly) {
            $q->where(function ($w) {
                $w->whereNull('last_review')->orWhere('last_review', '<', now()->subMonths(12));
            });
        }

        $teachers = $q->orderBy('name')->get();

        $rows = $teachers->map(function (Teacher $t) {
            return [
                't'         => $t,
                'attn'      => $t->attendanceRate(90),
                'obs'       => $t->observationAverage(3),
                'open'      => $t->openQueryLettersCount(),
                'goals'     => $t->goalsProgressAverage(),
                'tenure'    => $t->tenureYears(),
                'readiness' => $t->promotionReadiness(),
                'pinned'    => $t->hasPinnedNotes(),
                'onLeaveT'  => $t->isOnLeaveOn(now()->startOfDay()),
                'onLeaveW'  => $t->isOnLeaveBetween(now()->startOfWeek(), now()->endOfWeek()),
                'missing'   => $t->hasMissingRequiredDocs(),
            ];
        })->values();

        $rows = $rows->filter(function ($r) {
            if ($this->readiness && $r['readiness']['level'] !== $this->readiness) return false;
            if ($this->openQueriesOnly && $r['open'] === 0) return false;
            if ($this->pinnedNotesOnly && ! $r['pinned']) return false;
            if ($this->missingDocsOnly && ! $r['missing']) return false;
            if ($this->onLeaveWindow === 'today' && ! $r['onLeaveT']) return false;
            if ($this->onLeaveWindow === 'week'  && ! $r['onLeaveW']) return false;
            if ($this->attendanceBand) {
                $a = $r['attn'];
                if ($a === null) return false;
                if ($this->attendanceBand === 'lt75' && $a >= 75) return false;
                if ($this->attendanceBand === '75to90' && ($a < 75 || $a >= 90)) return false;
                if ($this->attendanceBand === 'gte90' && $a < 90) return false;
            }
            if ($this->obsBand) {
                $o = $r['obs'];
                if ($o === null) return false;
                if ($this->obsBand === 'lt25' && $o >= 2.5) return false;
                if ($this->obsBand === '25to35' && ($o < 2.5 || $o >= 3.5)) return false;
                if ($this->obsBand === 'gte35' && $o < 3.5) return false;
            }
            return true;
        })->values();

        $rows = match ($this->sort) {
            'tenure'    => $rows->sortByDesc('tenure')->values(),
            'obs'       => $rows->sortByDesc(fn ($r) => $r['obs'] ?? -1)->values(),
            'attn'      => $rows->sortByDesc(fn ($r) => $r['attn'] ?? -1)->values(),
            'readiness' => $rows->sortBy(fn ($r) => ['ready'=>0,'developing'=>1,'not_ready'=>2][$r['readiness']['level']] ?? 3)->values(),
            default     => $rows->sortBy(fn ($r) => $r['t']->name)->values(),
        };

        return $rows->all();
    }

    protected function pluckDistinct(string $col): array
    {
        return Teacher::query()->whereNotNull($col)->where($col, '!=', '')
            ->distinct()->orderBy($col)->pluck($col, $col)->all();
    }

    protected function getViewData(): array
    {
        return [
            'rows'              => $this->buildRows(),
            'savedViews'        => auth()->check()
                ? TeacherReviewSavedView::where('user_id', auth()->id())
                    ->orderByDesc('is_default')->orderBy('name')->get()
                : collect(),
            'campusOptions'     => $this->pluckDistinct('campus'),
            'deptOptions'       => $this->pluckDistinct('dept'),
            'subjectOptions'    => $this->pluckDistinct('subject'),
            'employmentOptions' => $this->pluckDistinct('employment'),
            'statusOptions'     => Teacher::STATUSES,
            'genderOptions'     => ['male' => 'Male', 'female' => 'Female'],
            'readinessOptions'  => Teacher::PROMOTION_LEVELS,
            'tenureOptions'     => [
                'lt1' => '< 1 year', '1to3' => '1–3 yrs',
                '3to7' => '3–7 yrs', 'gt7' => '7+ yrs',
            ],
            'attendanceOptions' => ['lt75' => '< 75%', '75to90' => '75–90%', 'gte90' => '90%+'],
            'obsOptions'        => ['lt25' => '< 2.5', '25to35' => '2.5–3.5', 'gte35' => '3.5+'],
            'leaveOptions'      => ['today' => 'On leave today', 'week' => 'On leave this week'],
            'compareUrl'        => TeacherResource::getUrl('compare', ['tab' => 'compare']),
            'leaderboardUrl'    => TeacherResource::getUrl('compare', ['tab' => 'leaderboard']),
            'sortOptions'       => [
                'name' => 'Name', 'tenure' => 'Tenure', 'obs' => 'Observation',
                'attn' => 'Attendance', 'readiness' => 'Readiness',
            ],
        ];
    }
}
