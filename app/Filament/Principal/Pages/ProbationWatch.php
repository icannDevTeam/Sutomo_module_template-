<?php

namespace App\Filament\Principal\Pages;

use App\Models\AuditLog;
use App\Models\DutyAssignment;
use App\Models\ObservationSetting;
use App\Models\Teacher;
use App\Models\TeacherContract;
use App\Models\TeacherObservation;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

/**
 * Phase 3 — Probation Watch.
 *
 * Lists every PKWT-I contract whose probation window is currently open
 * (probation_starts_at <= today <= probation_ends_at). For each, shows the
 * supervision/peer-observation counts vs configured minimums and lets the
 * Principal record the probation decision (continue / terminate).
 *
 * Reads thresholds from ObservationSetting; reads observation counts from
 * the Teacher Observation module via Phase 1 scopes.
 */
class ProbationWatch extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Contract Management';
    protected static ?string $title = 'Probation Watch';
    protected static ?int $navigationSort = 1; // above Contract Continuation
    protected static string $view = 'filament.principal.pages.probation-watch';

    #[Url]
    public ?string $academicYear = null;

    public function mount(): void
    {
        $this->academicYear ??= DutyAssignment::currentAcademicYear();
    }

    protected function settings(): ObservationSetting
    {
        return ObservationSetting::current();
    }

    /**
     * Returns one summary row per active probation contract.
     */
    protected function rows(): Collection
    {
        return TeacherContract::query()
            ->probationActive()
            ->with('teacher')
            ->orderBy('probation_ends_at')
            ->get()
            ->map(function (TeacherContract $c) {
                $teacher = $c->teacher;
                $sem1Ay = $c->academic_year ?: DutyAssignment::academicYearFor($c->starts_at);

                $supervisions = $teacher
                    ? TeacherObservation::query()
                        ->where('teacher_id', $teacher->id)
                        ->byType('principal_supervision')
                        ->where('observed_at', '>=', $c->probation_starts_at)
                        ->where('observed_at', '<=', now())
                        ->count()
                    : 0;

                $peerObs = $teacher
                    ? TeacherObservation::query()
                        ->where('teacher_id', $teacher->id)
                        ->byType('peer_observation')
                        ->where('observed_at', '>=', $c->probation_starts_at)
                        ->where('observed_at', '<=', now())
                        ->count()
                    : 0;

                $daysLeft = (int) Carbon::today()->diffInDays($c->probation_ends_at, false);

                return (object) [
                    'contract'      => $c,
                    'teacher'       => $teacher,
                    'supervisions'  => $supervisions,
                    'peer_obs'      => $peerObs,
                    'days_left'     => $daysLeft,
                ];
            });
    }

    public function recordContinue(int $contractId): void
    {
        $c = TeacherContract::query()->whereKey($contractId)->firstOrFail();
        if ($c->type !== 'pkwt_1' || ! is_null($c->probation_decision)) {
            Notification::make()->title('Decision already recorded or not a PKWT-I')->danger()->send();
            return;
        }
        $c->update([
            'probation_decision'    => 'continue',
            'probation_decision_at' => now(),
        ]);
        $this->writeAudit($c, 'continue', null);
        Notification::make()->title('Continuation recorded — PKWT-II will be spawned by Renewal flow')->success()->send();
    }

    public function recordTerminate(int $contractId): void
    {
        $c = TeacherContract::query()->whereKey($contractId)->firstOrFail();
        if ($c->type !== 'pkwt_1' || ! is_null($c->probation_decision)) {
            Notification::make()->title('Decision already recorded or not a PKWT-I')->danger()->send();
            return;
        }
        $c->update([
            'probation_decision'                  => 'terminate',
            'probation_decision_at'               => now(),
            'commitment_fee_refund_triggered_at'  => now(),
            'status'                              => 'terminated',
        ]);

        // Cascade teacher status.
        if ($c->teacher) {
            $c->teacher->update(['status' => 'not_renewed']);
        }

        $this->writeAudit($c, 'terminate', 'Commitment fee refund flagged for Finance.');
        Notification::make()->title('Probation terminated · refund triggered')->success()->send();
    }

    protected function writeAudit(TeacherContract $c, string $decision, ?string $note): void
    {
        try {
            AuditLog::create([
                'occurred_at' => now(),
                'user_name'   => auth()->user()?->name ?? 'System',
                'role'        => 'Principal',
                'action'      => 'probation.' . $decision,
                'target'      => 'TeacherContract:' . $c->id,
                'from_value'  => 'pkwt_1',
                'to_value'    => $decision,
                'note'        => $note,
            ]);
        } catch (\Throwable $e) {
            // audit write best-effort; never block the workflow
        }
    }

    public function getViewData(): array
    {
        $settings = $this->settings();
        $rows = $this->rows();
        $minSup = (int) $settings->probation_min_supervisions;
        $minPeer = (int) $settings->probation_min_peer_observations;
        $dueDays = (int) $settings->probation_decision_due_days;

        $stats = [
            'total'    => $rows->count(),
            'due'      => $rows->filter(fn ($r) => $r->days_left <= $dueDays)->count(),
            'missing'  => $rows->filter(fn ($r) => $r->supervisions < $minSup || $r->peer_obs < $minPeer)->count(),
            'pending'  => $rows->filter(fn ($r) => is_null($r->contract->probation_recommendation_path))->count(),
        ];

        return [
            'enabled'  => (bool) $settings->probation_watch_enabled,
            'rows'     => $rows,
            'stats'    => $stats,
            'minSup'   => $minSup,
            'minPeer'  => $minPeer,
            'dueDays'  => $dueDays,
        ];
    }
}
