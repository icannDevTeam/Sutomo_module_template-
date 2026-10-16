<?php

namespace App\Support;

use App\Models\ContractApprovalRequest;
use App\Models\ContractRequestEvent;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Pure presentation logic for a ContractApprovalRequest.
 *
 * Blade templates must never compute status, urgency, step state or
 * available actions themselves — they ask this presenter.
 */
class ContractRequestPresenter
{
    /** Ordered macro-steps shown on the stage track. */
    public const TRACK = [
        ['key' => 'submitted',  'label' => 'Submitted',   'stages' => ['submitted', 'correction_requested']],
        ['key' => 'review',     'label' => 'In review',   'stages' => ['under_review']],
        ['key' => 'approved',   'label' => 'Approved',    'stages' => ['approved']],
        ['key' => 'prep',       'label' => 'HR prep',     'stages' => ['hr_assigned', 'hr_in_progress']],
        ['key' => 'doc_review', 'label' => 'Doc review',  'stages' => ['document_uploaded', 'document_correction']],
        ['key' => 'accepted',   'label' => 'Accepted',    'stages' => ['document_accepted']],
        ['key' => 'signed',     'label' => 'Signed',      'stages' => ['signed_scheduled', 'hr_registration']],
        ['key' => 'complete',   'label' => 'Complete',    'stages' => ['completed']],
    ];

    /** Heroicon per stage; status is never colour-only. */
    public const STAGE_ICONS = [
        'draft'                => 'heroicon-m-pencil',
        'submitted'            => 'heroicon-m-paper-airplane',
        'under_review'         => 'heroicon-m-magnifying-glass',
        'correction_requested' => 'heroicon-m-exclamation-triangle',
        'approved'             => 'heroicon-m-check-circle',
        'rejected'             => 'heroicon-m-x-circle',
        'hr_assigned'          => 'heroicon-m-user',
        'hr_in_progress'       => 'heroicon-m-wrench-screwdriver',
        'document_uploaded'    => 'heroicon-m-document-arrow-up',
        'document_correction'  => 'heroicon-m-arrow-uturn-left',
        'document_accepted'    => 'heroicon-m-document-check',
        'signed_scheduled'     => 'heroicon-m-calendar-days',
        'hr_registration'      => 'heroicon-m-clipboard-document-check',
        'completed'            => 'heroicon-m-check-badge',
        'cancelled'            => 'heroicon-m-no-symbol',
    ];

    /** Maps STAGE_COLORS palette names to semantic tokens. */
    public const TONE = [
        'gray'    => 'neutral',
        'warning' => 'waiting',
        'info'    => 'review',
        'success' => 'approved',
        'danger'  => 'rejected',
    ];

    /**
     * Action catalogue. Tier: 0 none, 1 light, 2 reasoned, 3 committed.
     * Variant: primary | secondary | ghost | danger.
     */
    public const ACTIONS = [
        'start_review'        => ['label' => 'Start review',            'tier' => 0, 'variant' => 'ghost',     'icon' => 'heroicon-m-magnifying-glass'],
        'take_over_review'    => ['label' => 'Take over review',        'tier' => 1, 'variant' => 'ghost',     'icon' => 'heroicon-m-arrow-path-rounded-square'],
        'approve'             => ['label' => 'Approve',                 'tier' => 3, 'variant' => 'primary',   'icon' => 'heroicon-m-check-circle'],
        'request_correction'  => ['label' => 'Request correction',      'tier' => 2, 'variant' => 'secondary', 'icon' => 'heroicon-m-arrow-uturn-left'],
        'reject'              => ['label' => 'Reject',                  'tier' => 3, 'variant' => 'danger',    'icon' => 'heroicon-m-x-circle'],
        'assign_hr'           => ['label' => 'Assign HR',               'tier' => 2, 'variant' => 'primary',   'icon' => 'heroicon-m-user-plus'],
        'reassign_hr'         => ['label' => 'Reassign HR',             'tier' => 2, 'variant' => 'secondary', 'icon' => 'heroicon-m-arrows-right-left'],
        'nudge_hr'            => ['label' => 'Send reminder',           'tier' => 0, 'variant' => 'ghost',     'icon' => 'heroicon-m-bell-alert'],
        'mark_in_progress'    => ['label' => 'Start preparation',       'tier' => 0, 'variant' => 'secondary', 'icon' => 'heroicon-m-play'],
        'upload_document'     => ['label' => 'Upload document',         'tier' => 2, 'variant' => 'primary',   'icon' => 'heroicon-m-document-arrow-up'],
        'accept_document'     => ['label' => 'Accept document',         'tier' => 3, 'variant' => 'primary',   'icon' => 'heroicon-m-document-check'],
        'request_doc_fix'     => ['label' => 'Request correction',      'tier' => 2, 'variant' => 'secondary', 'icon' => 'heroicon-m-arrow-uturn-left'],
        'resubmit'            => ['label' => 'Edit and resubmit',       'tier' => 2, 'variant' => 'primary',   'icon' => 'heroicon-m-paper-airplane'],
        'withdraw'            => ['label' => 'Withdraw request',        'tier' => 2, 'variant' => 'danger',    'icon' => 'heroicon-m-no-symbol'],
        'acknowledge_registration' => ['label' => 'Confirm registration', 'tier' => 3, 'variant' => 'primary', 'icon' => 'heroicon-m-clipboard-document-check'],
        'sign'                => ['label' => 'Sign document',           'tier' => 3, 'variant' => 'primary',   'icon' => 'heroicon-m-pencil-square'],
    ];

    public function __construct(public readonly ContractApprovalRequest $request)
    {
    }

    public static function for(ContractApprovalRequest $request): self
    {
        return new self($request);
    }

    // ---------- Labels & tones ----------

    public function stageLabel(): string
    {
        return ContractApprovalRequest::STAGES[$this->request->stage] ?? $this->request->stage;
    }

    public function stageTone(): string
    {
        $palette = ContractApprovalRequest::STAGE_COLORS[$this->request->stage] ?? 'gray';

        if ($this->request->stage === 'completed') {
            return 'complete';
        }

        return self::TONE[$palette] ?? 'neutral';
    }

    public function stageIcon(): string
    {
        return self::STAGE_ICONS[$this->request->stage] ?? 'heroicon-m-ellipsis-horizontal-circle';
    }

    public function typeLabel(): string
    {
        return ContractApprovalRequest::TYPES[$this->request->type] ?? $this->request->type;
    }

    public function typeShort(): string
    {
        return $this->request->type === 'pkwt_renewal' ? 'PKWT' : 'Continuation';
    }

    public function tierLabel(?string $type): ?string
    {
        if (! $type) {
            return null;
        }

        return match ($type) {
            'pkwt_1'  => 'PKWT-I',
            'pkwt_2'  => 'PKWT-II',
            'pkwt_3'  => 'PKWT-III',
            'guru_sk' => 'Guru SK',
            default   => strtoupper(str_replace('_', '-', $type)),
        };
    }

    /** "Waiting on: HR (Sari W.)" style label with a named person when known. */
    public function waitingOn(): ?string
    {
        $r = $this->request;
        $owner = ContractApprovalRequest::STAGE_OWNER[$r->stage] ?? null;

        if ($owner === null) {
            return null;
        }

        return match ($owner) {
            'HR'      => $r->assignedHr ? "HR · {$r->assignedHr->name}" : 'HR',
            'Teacher' => $r->teacher ? "Teacher · {$r->teacher->name}" : 'Teacher',
            'Yayasan' => $r->stage === 'under_review' && $r->reviewer ? "Yayasan · {$r->reviewer->name}" : 'Yayasan',
            'Principal' => $r->submitter ? "Principal · {$r->submitter->name}" : 'Principal',
            default   => $owner,
        };
    }

    public function ownerRole(): ?string
    {
        return ContractApprovalRequest::STAGE_OWNER[$this->request->stage] ?? null;
    }

    public function isWaitingOn(User $user): bool
    {
        $r = $this->request;

        return match ($this->ownerRole()) {
            'Principal' => $r->submitted_by === $user->id,
            'HR'        => $r->assigned_hr_id === $user->id,
            'Yayasan'   => in_array($user->role, User::YAYASAN_ROLES, true),
            'Teacher'   => $r->teacher?->user_id === $user->id,
            default     => false,
        };
    }

    // ---------- Ageing / urgency ----------

    /** Timestamp the current stage has been waiting since. */
    public function waitingSince(): ?Carbon
    {
        $r = $this->request;

        return match ($r->stage) {
            'submitted'            => $this->lastEventAt(['submission.resubmitted', 'submission.created']) ?? $r->submitted_at,
            'under_review'         => $r->review_started_at,
            'correction_requested' => $this->lastEventAt(['correction.requested']),
            'approved'             => $r->decided_at,
            'hr_assigned', 'hr_in_progress' => $r->hr_assigned_at,
            'document_uploaded'    => $r->latestDocument?->uploaded_at,
            'document_correction'  => $this->lastEventAt(['document.correction']),
            'document_accepted'    => $r->latestDocument?->accepted_at,
            'signed_scheduled', 'hr_registration' => $r->latestDocument?->signed_at,
            default                => null,
        };
    }

    public function ageDays(): ?int
    {
        $since = $this->waitingSince();

        return $since ? (int) $since->diffInDays(now()) : null;
    }

    /** none | attention | overdue, using config thresholds per stage family. */
    public function urgency(): string
    {
        if ($this->request->isTerminal()) {
            return 'none';
        }

        $days = $this->ageDays();
        if ($days === null) {
            return 'none';
        }

        [$attention, $overdue] = match ($this->request->stage) {
            'submitted', 'under_review' => [config('approvals.review_attention_days'), config('approvals.review_overdue_days')],
            'approved'                  => [config('approvals.assign_attention_days'), config('approvals.assign_overdue_days')],
            'document_uploaded'         => [config('approvals.principal_review_attention_days'), config('approvals.principal_review_overdue_days')],
            default                     => [null, null],
        };

        // HR preparation uses the explicit due date instead of ageing.
        if (in_array($this->request->stage, ['hr_assigned', 'hr_in_progress', 'document_correction'], true)) {
            $due = $this->request->hr_preparation_due_at;
            if ($due && $due->isPast()) {
                return 'overdue';
            }
            if ($due && $due->diffInDays(now(), false) >= -1) {
                return 'attention';
            }

            return 'none';
        }

        if ($overdue !== null && $days >= $overdue) {
            return 'overdue';
        }
        if ($attention !== null && $days >= $attention) {
            return 'attention';
        }

        return 'none';
    }

    public function ageLabel(): ?string
    {
        $days = $this->ageDays();
        if ($days === null) {
            return null;
        }

        return match (true) {
            $days === 0 => 'Today',
            $days === 1 => '1 day',
            default     => "{$days} days",
        };
    }

    public function dueLabel(): ?string
    {
        $due = $this->request->hr_preparation_due_at;
        if (! $due || $this->request->isTerminal()) {
            return null;
        }
        if ($due->isToday()) {
            return 'Due today';
        }
        if ($due->isPast()) {
            return 'Overdue ' . $due->diffForHumans(null, true);
        }

        return 'Due ' . $due->format('d M');
    }

    // ---------- Stage track ----------

    /**
     * @return array<int, array{key:string,label:string,state:string,rounds:int}>
     * state: done | current | pending | failed
     */
    public function stageTrack(): array
    {
        $r = $this->request;
        $stage = $r->stage;
        $isRejected = $stage === 'rejected';
        $isCancelled = $stage === 'cancelled';

        // Where did a terminal failure happen? Use the last from_stage in the events.
        $failedAtStage = null;
        if ($isRejected || $isCancelled) {
            $evt = $r->events
                ->whereIn('event_type', ['decision.rejected', 'cancellation.marked', 'request.withdrawn'])
                ->last();
            $failedAtStage = $evt?->from_stage ?? 'submitted';
        }

        $currentIdx = null;
        foreach (self::TRACK as $i => $step) {
            if (in_array($failedAtStage ?? $stage, $step['stages'], true)) {
                $currentIdx = $i;
                break;
            }
        }
        if ($currentIdx === null) {
            $currentIdx = $stage === 'completed' ? count(self::TRACK) - 1 : 0;
        }

        $correctionRounds = $r->events->where('event_type', 'correction.requested')->count();
        $docRounds        = $r->events->where('event_type', 'document.correction')->count();

        $out = [];
        foreach (self::TRACK as $i => $step) {
            $state = match (true) {
                ($isRejected || $isCancelled) && $i === $currentIdx => 'failed',
                $i < $currentIdx                                     => 'done',
                $i === $currentIdx && $stage === 'completed'         => 'done',
                $i === $currentIdx                                   => 'current',
                default                                              => 'pending',
            };

            $rounds = match ($step['key']) {
                'submitted'  => $correctionRounds,
                'doc_review' => $docRounds,
                default      => 0,
            };

            $out[] = ['key' => $step['key'], 'label' => $step['label'], 'state' => $state, 'rounds' => $rounds];
        }

        return $out;
    }

    // ---------- Activity ----------

    public function lastEvent(): ?ContractRequestEvent
    {
        return $this->request->events->sortByDesc('occurred_at')->first();
    }

    public function lastActivityLabel(): ?string
    {
        $e = $this->lastEvent();
        if (! $e) {
            return null;
        }

        $what = ContractRequestEvent::TYPES[$e->event_type] ?? $e->event_type;

        return "{$what} · {$e->occurred_at?->diffForHumans()} by {$e->actor_name}";
    }

    public function lastNoteOfType(array $types): ?string
    {
        return $this->request->events->whereIn('event_type', $types)->last()?->note;
    }

    private function lastEventAt(array $types): ?Carbon
    {
        return $this->request->events->whereIn('event_type', $types)->last()?->occurred_at;
    }

    // ---------- Actions ----------

    /**
     * Full action set valid for this user at this stage, already policy-filtered.
     *
     * @return Collection<int, array{key:string,label:string,tier:int,variant:string,icon:string}>
     */
    public function availableActionsFor(User $user, bool $submitterOnly = false): Collection
    {
        $r = $this->request;
        $role = $user->role ?? 'teacher';
        $keys = [];

        $isYayasan   = in_array($role, [...User::YAYASAN_ROLES, ...User::HR_SIMULATION_ROLES], true);
        $isSubmitter = $r->submitted_by === $user->id;
        $isAssignee  = $r->assigned_hr_id === $user->id;
        $isTeacher   = $r->teacher?->user_id === $user->id;
        $isHrRole    = in_array($role, ['hr', 'admin', 'superadmin'], true);
        $isHrSimulation = in_array($role, User::HR_SIMULATION_ROLES, true) && $r->assigned_hr_id !== null;

        if ($isYayasan && ! $submitterOnly) {
            switch ($r->stage) {
                case 'submitted':
                    $keys = ['start_review', 'approve', 'request_correction', 'reject'];
                    break;
                case 'under_review':
                    $keys = $r->review_started_by === $user->id
                        ? ['approve', 'request_correction', 'reject']
                        : ['take_over_review', 'approve', 'request_correction', 'reject'];
                    break;
                case 'approved':
                    $keys = ['assign_hr'];
                    break;
                case 'hr_assigned':
                case 'hr_in_progress':
                case 'document_correction':
                    $keys = ['nudge_hr', 'reassign_hr'];
                    break;
            }
        }

        if ($isSubmitter) {
            switch ($r->stage) {
                case 'correction_requested':
                    $keys = array_merge($keys, ['resubmit', 'withdraw']);
                    break;
                case 'document_uploaded':
                    $keys = array_merge($keys, ['accept_document', 'request_doc_fix']);
                    break;
                case 'submitted':
                case 'under_review':
                    $keys = array_merge($keys, ['withdraw']);
                    break;
            }
        }

        if (! $submitterOnly && (($isAssignee && $isHrRole) || $isHrSimulation)) {
            switch ($r->stage) {
                case 'hr_assigned':
                    $keys = array_merge($keys, ['mark_in_progress', 'upload_document']);
                    break;
                case 'hr_in_progress':
                case 'document_correction':
                    $keys = array_merge($keys, ['upload_document']);
                    break;
                case 'hr_registration':
                case 'signed_scheduled':
                    $keys = array_merge($keys, ['acknowledge_registration']);
                    break;
            }
        }

        if (! $submitterOnly && $isTeacher && $r->stage === 'document_accepted') {
            $keys[] = 'sign';
        }

        return collect(array_values(array_unique($keys)))
            ->map(fn (string $k) => ['key' => $k] + self::ACTIONS[$k]);
    }

    /** The single most relevant action for a compact row, or null. */
    public function primaryActionFor(User $user, bool $submitterOnly = false): ?array
    {
        $actions = $this->availableActionsFor($user, $submitterOnly);

        return $actions->firstWhere('variant', 'primary')
            ?? $actions->first(fn ($a) => $a['variant'] === 'secondary')
            ?? null;
    }

    public static function action(string $key): array
    {
        return ['key' => $key] + (self::ACTIONS[$key] ?? ['label' => $key, 'tier' => 1, 'variant' => 'secondary', 'icon' => 'heroicon-m-cursor-arrow-rays']);
    }
}
