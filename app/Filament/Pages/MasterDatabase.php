<?php

namespace App\Filament\Pages;

use App\Filament\Resources\TeacherResource;
use App\Models\Teacher;
use App\Support\CsvExporter;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class MasterDatabase extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?string $navigationGroup = 'People';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.master-database';
    protected static ?string $title = 'Master Database';
    protected static ?string $navigationLabel = 'Master Database';

    public const STATUSES = [
        'permanent' => 'Permanent', 'contract' => 'Contract',
        'opl' => 'OPL', 'probation' => 'Probation',
        'leave' => 'On Leave', 'alumni' => 'Alumni',
    ];

    public const STATUS_COLOR = [
        'permanent' => 'success', 'contract' => 'info', 'opl' => 'warning',
        'probation' => 'warning', 'leave' => 'purple', 'alumni' => 'gray',
    ];

    public const EMPLOYMENTS = [
        'full-time' => 'Full-time', 'part-time' => 'Part-time',
        'contract'  => 'Contract',  'guest'     => 'Guest',
    ];

    #[Url(as: 'q')]
    public string $q = '';
    #[Url] public string $dept = 'all';
    #[Url] public string $campus = 'all';
    #[Url] public string $status = 'all';
    #[Url] public string $employment = 'all';
    #[Url] public string $sort = 'name';

    public function resetFilters(): void
    {
        $this->q = ''; $this->dept = 'all'; $this->campus = 'all';
        $this->status = 'all'; $this->employment = 'all'; $this->sort = 'name';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Export CSV')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray')
                ->action(fn () => CsvExporter::download(
                    $this->buildQuery()->get(),
                    TeacherResource::csvColumns(),
                    CsvExporter::filename('master-database'),
                )),
        ];
    }

    protected function buildQuery()
    {
        $q = Teacher::query();

        if ($this->q !== '') {
            $term = '%' . strtolower($this->q) . '%';
            $q->where(function ($qq) use ($term) {
                $qq->whereRaw('lower(name) like ?', [$term])
                   ->orWhereRaw('lower(code) like ?', [$term])
                   ->orWhereRaw('lower(employee_no) like ?', [$term])
                   ->orWhereRaw('lower(email) like ?', [$term])
                   ->orWhereRaw('lower(subject) like ?', [$term])
                   ->orWhereRaw('lower(dept) like ?', [$term]);
            });
        }
        if ($this->dept !== 'all')       $q->where('dept', $this->dept);
        if ($this->campus !== 'all')     $q->where('campus', $this->campus);
        if ($this->status !== 'all')     $q->where('status', $this->status);
        if ($this->employment !== 'all') $q->where('employment', $this->employment);

        $q->orderBy(match ($this->sort) {
            'joined', 'tenure' => 'joined_at',
            'rating'           => 'rating',
            'campus'           => 'campus',
            default            => 'name',
        }, $this->sort === 'rating' ? 'desc' : 'asc');

        return $q;
    }

    public function getViewData(): array
    {
        $list = $this->buildQuery()->get();
        $all  = Teacher::all();
        $counts = $all->groupBy('status')->map->count();

        return [
            'list'    => $list,
            'total'   => $all->count(),
            'counts'  => $counts,
            'depts'   => Teacher::query()->whereNotNull('dept')->distinct()->orderBy('dept')->pluck('dept'),
            'campuses'=> ['sd' => 'SD', 'smp' => 'SMP', 'sma' => 'SMA', 'int' => 'International'],
        ];
    }
}
