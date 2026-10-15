<?php

namespace App\Filament\Yayasan\Pages;

use App\Models\ContractApprovalRequest;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class HistoryPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Overview';
    protected static ?string $title = 'Decision History';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.yayasan.pages.history-page';

    #[Url]
    public string $typeFilter = 'all';

    #[Url]
    public string $decisionFilter = 'all'; // all | approved | rejected

    public function getViewData(): array
    {
        $q = ContractApprovalRequest::with(['teacher', 'decider', 'submitter', 'events'])
            ->whereIn('stage', ['completed', 'rejected', 'cancelled'])
            ->orderByDesc('decided_at');

        if ($this->typeFilter !== 'all') {
            $q->where('type', $this->typeFilter);
        }
        if ($this->decisionFilter !== 'all') {
            $q->where('decision', $this->decisionFilter);
        }

        return [
            'requests' => $q->limit(100)->get(),
        ];
    }
}
