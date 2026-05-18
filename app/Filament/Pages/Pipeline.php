<?php

namespace App\Filament\Pages;

use App\Models\AuditLog;
use App\Models\Candidate;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;

class Pipeline extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-view-columns';
    protected static ?string $navigationGroup = 'Hiring';
    protected static ?int $navigationSort = 0;
    protected static string $view = 'filament.pages.pipeline';
    protected static ?string $title = 'Hiring Pipeline';
    protected static ?string $slug = 'pipeline';

    public ?string $vacancyFilter = null;

    public function getColumns(): array
    {
        return [
            'applied'   => ['label' => 'Applied',         'color' => 'gray',    'hex' => '#64748b'],
            'screening' => ['label' => 'Screening',       'color' => 'sky',     'hex' => '#0ea5e9'],
            'written'   => ['label' => 'Written Test',    'color' => 'sky',     'hex' => '#0284c7'],
            'interview' => ['label' => 'Interview',       'color' => 'amber',   'hex' => '#f59e0b'],
            'psycho'    => ['label' => 'Psycho Test',     'color' => 'blue',    'hex' => '#3b82f6'],
            'medical'   => ['label' => 'Medical',         'color' => 'emerald', 'hex' => '#10b981'],
            'yayasan'   => ['label' => 'Yayasan Review',  'color' => 'rose',    'hex' => '#f43f5e'],
            'opl'       => ['label' => 'OPL Probation',   'color' => 'orange',  'hex' => '#f97316'],
        ];
    }

    public function getCandidatesByStage(): array
    {
        $q = Candidate::with(['vacancy', 'deposit'])->whereIn('stage', array_keys($this->getColumns()));
        if ($this->vacancyFilter) $q->where('vacancy_id', $this->vacancyFilter);
        $rows = $q->orderBy('applied_at', 'desc')->get()->groupBy('stage');
        $out = [];
        foreach (array_keys($this->getColumns()) as $stage) {
            $out[$stage] = $rows->get($stage, collect());
        }
        return $out;
    }

    public function moveCandidate(int $candidateId, string $newStage): void
    {
        if (!array_key_exists($newStage, Candidate::STAGES)) return;
        $c = Candidate::find($candidateId);
        if (!$c || $c->stage === $newStage) return;

        $from = $c->stage;
        $c->stage = $newStage;
        $c->save();

        AuditLog::create([
            'occurred_at' => now(),
            'user_name'   => auth()->user()?->name ?? 'System',
            'role'        => 'HR',
            'action'      => 'stage.move',
            'target'      => $c->code,
            'from_value'  => $from,
            'to_value'    => $newStage,
            'note'        => "Drag-drop on Kanban board.",
        ]);

        Notification::make()
            ->title("Moved {$c->name}")
            ->body(Candidate::STAGES[$from] . ' → ' . Candidate::STAGES[$newStage])
            ->success()->send();
    }

    public function getVacancies(): array
    {
        return \App\Models\Vacancy::orderBy('title')->pluck('title', 'id')->toArray();
    }
}
