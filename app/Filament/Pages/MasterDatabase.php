<?php

namespace App\Filament\Pages;

use App\Filament\Resources\CandidateResource;
use App\Models\Candidate;
use App\Models\Vacancy;
use App\Support\CsvExporter;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class MasterDatabase extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?string $navigationGroup = 'People';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.master-database';
    protected static ?string $title = 'Master Database — Talent Pool';
    protected static ?string $navigationLabel = 'Master Database';

    public const SUBJECT_OPTIONS = [
        'Mathematics','Statistics','English','English Literature','Bahasa Indonesia',
        'Mandarin','Chinese Literature','Physics','Chemistry','Biology','Science',
        'Computer Science','ICT','Python','Visual Arts','Design','PE & Health',
        'Counseling','BK','History','Social Studies','TOK','Religion (Islam)',
        'Religion (Christian)','Arabic','Primary Class Teacher','Tematik','IB PYP',
    ];

    public const CAMPUSES = ['sd' => 'SD', 'smp' => 'SMP', 'sma' => 'SMA', 'int' => 'International'];

    #[Url(as: 'q')]     public string $q = '';
    #[Url] public string $subject       = 'all';
    #[Url] public string $qualification = 'all';
    #[Url] public string $source        = 'all';
    #[Url] public string $campus        = 'all';
    #[Url] public string $city          = 'all';
    #[Url] public string $availability  = 'all';
    #[Url] public string $shortlisted   = 'all';
    #[Url] public int    $minYears      = 0;
    #[Url] public int    $maxYears      = 30;
    #[Url] public string $sort          = 'recent';

    public function resetFilters(): void
    {
        $this->q = '';
        $this->subject = $this->qualification = $this->source = 'all';
        $this->campus = $this->city = $this->availability = $this->shortlisted = 'all';
        $this->minYears = 0; $this->maxYears = 30;
        $this->sort = 'recent';
    }

    public function toggleShortlist(int $id): void
    {
        $c = Candidate::find($id);
        if (! $c) return;
        $c->shortlisted = ! $c->shortlisted;
        $c->save();
        Notification::make()
            ->title($c->shortlisted ? 'Added to shortlist' : 'Removed from shortlist')
            ->body($c->name)
            ->success()
            ->send();
    }

    public function assignToVacancy(int $id, int $vacancyId): void
    {
        $c = Candidate::find($id);
        $v = Vacancy::find($vacancyId);
        if (! $c || ! $v) return;
        $c->vacancy_id = $v->id;
        $c->stage = $c->stage === 'applied' ? 'screening' : $c->stage;
        $c->save();
        Notification::make()
            ->title('Candidate assigned')
            ->body("{$c->name} → {$v->title}")
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Export pool (CSV)')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray')
                ->action(fn () => CsvExporter::download(
                    $this->buildQuery()->get(),
                    CandidateResource::csvColumns(),
                    CsvExporter::filename('talent-pool'),
                )),
        ];
    }

    protected function buildQuery(): Builder
    {
        $q = Candidate::query()->with('vacancy');

        if ($this->q !== '') {
            $term = '%' . strtolower($this->q) . '%';
            $q->where(function ($qq) use ($term) {
                $qq->whereRaw('lower(name) like ?', [$term])
                   ->orWhereRaw('lower(code) like ?', [$term])
                   ->orWhereRaw('lower(email) like ?', [$term])
                   ->orWhereRaw('lower(city) like ?', [$term])
                   ->orWhereRaw('lower(education) like ?', [$term])
                   ->orWhereRaw('lower(current_school) like ?', [$term])
                   ->orWhereRaw('lower(coalesce(notes,"")) like ?', [$term]);
            });
        }

        if ($this->subject !== 'all') {
            $term = '%"' . $this->subject . '"%';
            $q->where('subjects', 'like', $term);
        }
        if ($this->qualification !== 'all') $q->where('qualification', $this->qualification);
        if ($this->source !== 'all')        $q->where('source', $this->source);
        if ($this->campus !== 'all')        $q->where('preferred_campus', $this->campus);
        if ($this->city !== 'all')          $q->where('city', $this->city);
        if ($this->shortlisted === 'yes')   $q->where('shortlisted', true);
        if ($this->shortlisted === 'no')    $q->where('shortlisted', false);

        if ($this->availability !== 'all') {
            $q->where('availability', $this->availability);
        }

        $min = max(0, (int) $this->minYears);
        $max = max($min, (int) $this->maxYears);
        $q->whereBetween('years', [$min, $max]);

        $q = match ($this->sort) {
            'years'      => $q->orderByDesc('years'),
            'name'       => $q->orderBy('name'),
            'salary'     => $q->orderBy('desired_salary'),
            'shortlist'  => $q->orderByDesc('shortlisted')->orderByDesc('applied_at'),
            default      => $q->orderByDesc('applied_at'),
        };

        return $q;
    }

    public function getViewData(): array
    {
        $list = $this->buildQuery()->limit(500)->get();
        $all  = Candidate::query();

        $totals = [
            'pool'       => (clone $all)->count(),
            'shortlist'  => (clone $all)->where('shortlisted', true)->count(),
            'assigned'   => (clone $all)->whereNotNull('vacancy_id')->count(),
            'unassigned' => (clone $all)->whereNull('vacancy_id')->count(),
            'website'    => (clone $all)->where('source', 'website')->count(),
            'walkin'     => (clone $all)->where('source', 'walk-in')->count(),
        ];

        $cities         = Candidate::query()->whereNotNull('city')->distinct()->orderBy('city')->pluck('city');
        $availabilities = Candidate::query()->whereNotNull('availability')->distinct()->orderBy('availability')->pluck('availability');
        $vacancies      = Vacancy::whereIn('status', ['open', 'closing'])->orderBy('title')->get(['id','code','title']);

        return [
            'list'           => $list,
            'totals'         => $totals,
            'cities'         => $cities,
            'availabilities' => $availabilities,
            'vacancies'      => $vacancies,
            'subjects'       => self::SUBJECT_OPTIONS,
            'qualifications' => Candidate::QUALIFICATIONS,
            'sources'        => Candidate::SOURCES,
            'campuses'       => self::CAMPUSES,
        ];
    }
}
