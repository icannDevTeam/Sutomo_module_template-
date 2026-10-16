<?php

namespace App\Filament\Yayasan\Pages;

use App\Models\ContractApprovalRequest;
use Filament\Pages\Page;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;

class HistoryPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Approvals';
    protected static ?string $title = 'Decision History';
    protected static ?int $navigationSort = 10;
    protected static string $view = 'filament.yayasan.pages.history-page';

    #[Url]
    public string $typeFilter = 'all';

    #[Url]
    public string $decisionFilter = 'all';

    #[Url]
    public string $domainFilter = 'all'; // all | contracts | hiring

    public function getViewData(): array
    {
        $contracts = ContractApprovalRequest::with(['teacher', 'decider', 'submitter', 'latestRevision'])
            ->whereIn('stage', ['completed', 'rejected', 'cancelled'])
            ->orderByDesc('decided_at')
            ->when($this->typeFilter !== 'all', fn ($q) => $q->where('type', $this->typeFilter))
            ->when($this->decisionFilter !== 'all', fn ($q) => $q->where('decision', $this->decisionFilter))
            ->when($this->domainFilter === 'hiring', fn ($q) => $q->whereRaw('1=0'))
            ->limit(80)
            ->get();

        $hiring = \App\Models\Candidate::with('vacancy')
            ->where(function ($q) {
                $q->whereJsonContains('meta->yayasan->status', 'approved')
                  ->orWhereJsonContains('meta->yayasan->status', 'rejected');
            })
            ->when($this->domainFilter === 'contracts', fn ($q) => $q->whereRaw('1=0'))
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get();

        $contractStats = [
            'total'    => $contracts->count(),
            'approved' => $contracts->where('decision', 'approved')->count(),
            'rejected' => $contracts->where('decision', 'rejected')->count(),
        ];
        $hiringStats = [
            'total'    => $hiring->count(),
            'approved' => $hiring->filter(fn ($c) => ($c->meta['yayasan']['status'] ?? '') === 'approved')->count(),
            'rejected' => $hiring->filter(fn ($c) => ($c->meta['yayasan']['status'] ?? '') === 'rejected')->count(),
        ];

        return compact('contracts', 'hiring', 'contractStats', 'hiringStats');
    }
}
