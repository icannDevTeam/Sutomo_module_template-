<?php

namespace App\Filament\Concerns;

use App\Models\ContractApprovalRequest;
use App\Services\StaleRequestException;
use Filament\Notifications\Notification;

/**
 * Shared sheet (tiered confirmation) state for approval pages.
 *
 * $sheet        action key currently open, or null
 * $sheetFor     request id the sheet targets
 * $seenStage    stage the user saw when opening — passed as expectedStage
 * $seenDocId    latest document id seen when opening (document actions)
 * $sheetError   server-side validation / domain error to render inline
 * $sheetStale   stale-state message; disables the confirm button
 * $sheetSuccess success panel payload after a committed action
 * $reason       shared free-text input for Tier-2/3 sheets
 */
trait HasApprovalSheets
{
    public ?string $sheet = null;
    public ?int $sheetFor = null;
    public ?string $seenStage = null;
    public ?int $seenDocId = null;
    public ?string $sheetError = null;
    public ?string $sheetStale = null;
    public ?array $sheetSuccess = null;
    public string $reason = '';

    public function openSheet(int $requestId, string $action): void
    {
        $request = ContractApprovalRequest::with('latestDocument')->find($requestId);
        if (! $request) {
            Notification::make()->title('Request not found')->danger()->send();
            return;
        }

        $this->sheet        = $action;
        $this->sheetFor     = $requestId;
        $this->seenStage    = $request->stage;
        $this->seenDocId    = $request->latestDocument?->id;
        $this->sheetError   = null;
        $this->sheetStale   = null;
        $this->sheetSuccess = null;
        $this->reason       = '';

        if (method_exists($this, 'onSheetOpened')) {
            $this->onSheetOpened($request, $action);
        }
    }

    public function closeSheet(): void
    {
        $this->sheet = null;
        $this->sheetFor = null;
        $this->seenStage = null;
        $this->seenDocId = null;
        $this->sheetError = null;
        $this->sheetStale = null;
        $this->sheetSuccess = null;
        $this->reason = '';
    }

    public function isSheet(string $action): bool
    {
        return $this->sheet === $action;
    }

    /**
     * Run a workflow call with uniform error handling.
     * Returns true on success. On success either shows the given success
     * panel (keeps the sheet open) or closes the sheet and toasts.
     */
    protected function runGuarded(callable $fn, ?array $success = null, ?string $toast = null): bool
    {
        try {
            $fn();
        } catch (StaleRequestException $e) {
            $this->sheetStale = $e->getMessage();
            return false;
        } catch (\InvalidArgumentException $e) {
            $this->sheetError = $e->getMessage();
            return false;
        } catch (\RuntimeException $e) {
            $this->sheetError = $e->getMessage();
            return false;
        } catch (\Throwable $e) {
            report($e);
            $this->sheetError = 'Something went wrong on our side. Nothing was changed — please try again.';
            return false;
        }

        if ($success) {
            $this->sheetSuccess = $success;
            $this->sheetError = null;
            $this->sheetStale = null;
        } else {
            $this->closeSheet();
            if ($toast) {
                Notification::make()->title($toast)->success()->send();
            }
        }

        return true;
    }

    protected function sheetRequest(): ?ContractApprovalRequest
    {
        return $this->sheetFor ? ContractApprovalRequest::find($this->sheetFor) : null;
    }
}
