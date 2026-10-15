<?php

namespace App\Filament\Yayasan\Pages;

use App\Models\ContractApprovalRequest;
use App\Models\User;
use App\Services\ContractApprovalWorkflow;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class HrTasksOverview extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'HR Tasks';
    protected static ?string $title = 'HR Tasks Overview';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.yayasan.pages.hr-tasks-overview';

    // Reassign modal
    public ?int $reassignRequestId = null;
    public ?int $reassignHrUserId = null;
    public string $reassignReason = '';

    public function getViewData(): array
    {
        $tasks = ContractApprovalRequest::with(['teacher', 'assignedHr', 'latestDocument'])
            ->whereNotNull('assigned_hr_id')
            ->whereNotIn('stage', ['completed', 'cancelled', 'rejected'])
            ->orderBy('hr_preparation_due_at')
            ->get()
            ->groupBy('assigned_hr_id');

        $hrUsers = User::where('role', 'hr')->orderBy('name')->get();

        $overdue = ContractApprovalRequest::whereNotNull('assigned_hr_id')
            ->whereIn('stage', ['hr_assigned', 'hr_in_progress'])
            ->whereNotNull('hr_preparation_due_at')
            ->whereDate('hr_preparation_due_at', '<', now())
            ->count();

        return compact('tasks', 'hrUsers', 'overdue');
    }

    public function openReassignModal(int $id): void
    {
        $request = ContractApprovalRequest::find($id);
        $this->reassignRequestId = $id;
        $this->reassignHrUserId  = $request?->assigned_hr_id;
        $this->reassignReason    = '';
    }

    public function submitReassign(): void
    {
        if (! $this->reassignRequestId || ! $this->reassignHrUserId) {
            Notification::make()->title('Please select an HR user.')->warning()->send();
            return;
        }
        try {
            $request = ContractApprovalRequest::findOrFail($this->reassignRequestId);
            app(ContractApprovalWorkflow::class)->reassignHr(
                $request, auth()->user(),
                $this->reassignHrUserId,
                $this->reassignReason ?: 'Reassigned by Yayasan.'
            );
            $this->reassignRequestId = null;
            Notification::make()->title('Reassigned.')->success()->send();
        } catch (\Exception $e) {
            Notification::make()->title($e->getMessage())->danger()->send();
        }
    }
}
