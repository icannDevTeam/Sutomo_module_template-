<?php

namespace App\Services;

use App\Models\ContractApprovalRequest;

/**
 * Thrown when a request changed stage between the moment the user saw it
 * and the moment they committed an action. Pages render a "reloaded" notice
 * instead of silently applying a decision to a different state.
 */
class StaleRequestException extends \RuntimeException
{
    public function __construct(
        public readonly ContractApprovalRequest $request,
        public readonly ?string $expectedStage,
    ) {
        $seen = $expectedStage ? (ContractApprovalRequest::STAGES[$expectedStage] ?? $expectedStage) : 'an earlier state';
        $now  = ContractApprovalRequest::STAGES[$request->stage] ?? $request->stage;

        parent::__construct("You were viewing it as “{$seen}”; it is now “{$now}”.");
    }
}
