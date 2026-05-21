<?php

namespace App\Filament\Principal\Pages;

use App\Models\UnitPlan;
use App\Models\UnitPlanComment;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class UnitPlanning extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';
    protected static ?string $navigationGroup = 'Planning';
    protected static ?string $title = 'Unit Planning';
    protected static ?string $navigationLabel = 'Units & Topics';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.principal.pages.unit-planning';

    public ?int $selectedId = null;
    public string $filter = 'all'; // all|active|draft|completed|mine
    public string $q = '';
    public string $tab = 'planning'; // planning|implementing|flow|evidencing|reflecting
    public string $newComment = '';

    public function mount(): void
    {
        $this->selectedId = UnitPlan::query()
            ->orderByDesc('last_activity_at')
            ->value('id');
    }

    public function setFilter(string $f): void
    {
        $this->filter = $f;
    }

    public function select(int $id): void
    {
        $this->selectedId = $id;
        $this->tab = 'planning';
    }

    public function setTab(string $t): void
    {
        $this->tab = $t;
    }

    public function postComment(): void
    {
        $body = trim($this->newComment);
        if ($body === '' || ! $this->selectedId) {
            return;
        }
        UnitPlanComment::create([
            'unit_plan_id'    => $this->selectedId,
            'section_key'     => null,
            'author_name'     => 'Pak Dwi',
            'author_initials' => 'BS',
            'author_color'    => '#4338ca',
            'body'            => $body,
        ]);
        UnitPlan::where('id', $this->selectedId)->update(['last_activity_at' => Carbon::now()]);
        $this->newComment = '';
    }

    public function getViewData(): array
    {
        $base = UnitPlan::query()->with(['collaborators', 'comments']);

        $filtered = (clone $base)
            ->when($this->q !== '', fn ($q) => $q->where(function ($w) {
                $w->where('title', 'like', '%'.$this->q.'%')
                  ->orWhere('grade', 'like', '%'.$this->q.'%')
                  ->orWhere('theme', 'like', '%'.$this->q.'%');
            }))
            ->when($this->filter === 'active',    fn ($q) => $q->where('status', 'active'))
            ->when($this->filter === 'draft',     fn ($q) => $q->where('status', 'draft'))
            ->when($this->filter === 'completed', fn ($q) => $q->where('status', 'completed'))
            ->when($this->filter === 'mine',      fn ($q) => $q->where('owner_name', 'Pak Dwi'))
            ->orderByDesc('last_activity_at')
            ->get();

        $current = $this->selectedId ? UnitPlan::with(['collaborators', 'comments'])->find($this->selectedId) : null;

        $counts = [
            'all'       => UnitPlan::count(),
            'active'    => UnitPlan::where('status', 'active')->count(),
            'draft'     => UnitPlan::where('status', 'draft')->count(),
            'completed' => UnitPlan::where('status', 'completed')->count(),
            'mine'      => UnitPlan::where('owner_name', 'Pak Dwi')->count(),
        ];

        return [
            'plans'   => $filtered,
            'current' => $current,
            'counts'  => $counts,
        ];
    }
}
