<?php

namespace App\Filament\Pages;

use App\Filament\Resources\CandidateResource;
use App\Models\Candidate;
use App\Models\Interview;
use App\Support\CsvExporter;
use Filament\Actions\Action;
use Filament\Pages\Page;

class Assessments extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Hiring';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.pages.assessments';
    protected static ?string $title = 'Assessments';

    public string $activeTab = 'tests';

    public function setActiveTab(string $tab): void { $this->activeTab = $tab; }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportScores')
                ->label('Export scores')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray')
                ->action(fn () => CsvExporter::download(
                    Candidate::whereNotNull('score_written')->with('vacancy')->orderByDesc('score_written')->get(),
                    CandidateResource::csvColumns(),
                    CsvExporter::filename('assessment-scores'),
                )),
        ];
    }

    public function getViewData(): array
    {
        $writtenCands = Candidate::whereNotNull('score_written')->with('vacancy')->orderByDesc('score_written')->get();
        $scheduled    = Candidate::where('stage', 'written')->count();
        $awaiting     = Candidate::where('stage', 'written')->whereNull('score_written')->count();
        $passRate     = $writtenCands->count() ? round($writtenCands->where('score_written', '>=', 70)->count() / $writtenCands->count() * 100) : 0;
        $avg          = $writtenCands->count() ? round($writtenCands->avg('score_written')) : 0;

        // Score distribution buckets (0-49, 50-69, 70-84, 85-100)
        $buckets = [
            ['label' => 'Fail (<50)',    'min' => 0,  'max' => 49,  'tone' => 'rose'],
            ['label' => 'Marginal (50-69)', 'min' => 50, 'max' => 69,  'tone' => 'amber'],
            ['label' => 'Pass (70-84)',  'min' => 70, 'max' => 84,  'tone' => 'blue'],
            ['label' => 'Strong (85+)',  'min' => 85, 'max' => 100, 'tone' => 'emerald'],
        ];
        $maxBucket = 1;
        foreach ($buckets as &$b) {
            $b['count'] = $writtenCands->whereBetween('score_written', [$b['min'], $b['max']])->count();
            $maxBucket = max($maxBucket, $b['count']);
        }
        unset($b);

        $interviews = Interview::with('candidate')->orderByDesc('scheduled_date')->get();
        $today      = $interviews->filter(fn ($i) => $i->scheduled_date?->isToday())->values();
        $upcoming   = $interviews->filter(fn ($i) => $i->scheduled_date?->isFuture() && ! $i->scheduled_date?->isToday())->values();
        $completed  = $interviews->where('status', 'completed');

        $recBreak = [
            'strong' => $completed->where('recommendation', 'Strong Hire')->count(),
            'hire'   => $completed->where('recommendation', 'Hire')->count(),
            'maybe'  => $completed->where('recommendation', 'Maybe')->count(),
            'no'     => $completed->whereIn('recommendation', ['No Hire', 'Reject'])->count(),
        ];

        return [
            'writtenCands' => $writtenCands,
            'top5'         => $writtenCands->take(5),
            'scheduled'    => $scheduled,
            'awaiting'     => $awaiting,
            'passRate'     => $passRate,
            'avg'          => $avg,
            'buckets'      => $buckets,
            'maxBucket'    => $maxBucket,
            'interviews'   => $interviews,
            'todayIvs'     => $today,
            'upcomingIvs'  => $upcoming,
            'completedIvs' => $completed->count(),
            'recBreak'     => $recBreak,
        ];
    }
}
