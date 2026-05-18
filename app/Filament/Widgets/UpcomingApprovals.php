<?php

namespace App\Filament\Widgets;

use App\Models\Candidate;
use App\Models\Deposit;
use App\Models\Interview;
use Filament\Widgets\Widget;

class UpcomingApprovals extends Widget
{
    protected static string $view = 'filament.widgets.upcoming-approvals';
    protected int|string|array $columnSpan = ['default' => 'full', 'lg' => 2];
    protected static ?int $sort = 6;

    public function getViewData(): array
    {
        $items = collect();

        Candidate::where('stage', 'yayasan')->with('vacancy')->get()->each(function ($c) use ($items) {
            $items->push([
                'type'   => 'yayasan',
                'icon'   => '🏛',
                'color'  => 'rose',
                'title'  => $c->name,
                'sub'    => 'Yayasan approval — ' . ($c->vacancy?->title ?? '—'),
                'when'   => 'Pending board review',
                'url'    => \App\Filament\Resources\CandidateResource::getUrl('view', ['record' => $c]),
            ]);
        });

        Interview::with('candidate')
            ->where('status', 'scheduled')
            ->whereDate('scheduled_date', '>=', now()->toDateString())
            ->orderBy('scheduled_date')->limit(5)->get()
            ->each(function ($iv) use ($items) {
                $items->push([
                    'type'  => 'interview',
                    'icon'  => '🎤',
                    'color' => 'amber',
                    'title' => $iv->candidate?->name ?? 'Candidate',
                    'sub'   => $iv->type . ' · ' . $iv->room,
                    'when'  => $iv->scheduled_date?->format('d M') . ' · ' . $iv->scheduled_time,
                    'url'   => $iv->candidate ? \App\Filament\Resources\CandidateResource::getUrl('view', ['record' => $iv->candidate]) : '#',
                ]);
            });

        Deposit::with('candidate')->where('status', 'pending')->limit(5)->get()
            ->each(function ($d) use ($items) {
                $items->push([
                    'type'  => 'deposit',
                    'icon'  => '💰',
                    'color' => 'emerald',
                    'title' => $d->candidate?->name ?? 'Candidate',
                    'sub'   => 'Deposit verification · Rp ' . number_format($d->amount, 0, ',', '.'),
                    'when'  => 'Due ' . ($d->due_date?->format('d M') ?? 'soon'),
                    'url'   => \App\Filament\Resources\DepositResource::getUrl('edit', ['record' => $d]),
                ]);
            });

        return ['items' => $items->take(8)];
    }
}
