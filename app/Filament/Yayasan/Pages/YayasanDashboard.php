<?php

namespace App\Filament\Yayasan\Pages;

use App\Models\Candidate;
use App\Models\ContractApprovalRequest;
use Filament\Pages\Page;

class YayasanDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationGroup = 'Overview';
    protected static ?int $navigationSort = -10;
    protected static ?string $navigationLabel = 'Yayasan Dashboard';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'Dashboard';
    protected static string $view = 'filament.yayasan.pages.yayasan-dashboard';

    public function getViewData(): array
    {
        $pendingReview = ContractApprovalRequest::whereIn('stage', ['submitted', 'under_review'])->count();
        $correctionPending = ContractApprovalRequest::where('stage', 'correction_requested')->count();
        $pendingHrAssign = ContractApprovalRequest::where('stage', 'approved')->count();
        $hrInProgress = ContractApprovalRequest::whereIn('stage', ['hr_assigned', 'hr_in_progress', 'document_correction'])->count();
        $pendingHiring = Candidate::where('stage', 'yayasan')->count();

        $renewalPending = ContractApprovalRequest::where('type', 'pkwt_renewal')
            ->whereIn('stage', ['submitted', 'under_review'])->count();
        $continuationPending = ContractApprovalRequest::where('type', 'annual_continuation')
            ->whereIn('stage', ['submitted', 'under_review'])->count();

        $recentDecisions = ContractApprovalRequest::with(['teacher', 'decider'])
            ->whereNotNull('decided_at')
            ->orderByDesc('decided_at')
            ->limit(8)
            ->get();

        return compact(
            'pendingReview',
            'correctionPending',
            'pendingHrAssign',
            'hrInProgress',
            'pendingHiring',
            'renewalPending',
            'continuationPending',
            'recentDecisions'
        );
    }
}
