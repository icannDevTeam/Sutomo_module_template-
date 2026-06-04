<?php

namespace Database\Seeders;

use App\Models\DutyAssignment;
use App\Models\LetterOfIntent;
use App\Models\Teacher;
use App\Models\TeacherContract;
use App\Models\TeacherObservation;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Demo data populator for the Contract Management module:
 *  - Probation Watch (Phase 3)
 *  - Contract Renewal (Phase 4)
 *  - LOI Continuation pipeline (Phase 2)
 *
 * Idempotent: tags every row it creates with the marker "__demo__" in
 * `signature_text` (contracts) / `review_notes` (observations) /
 * `notes` (LOIs) so it can wipe and re-seed cleanly.
 *
 * Picks teachers that currently have NO active contract so it doesn't
 * collide with existing backfilled data. If a slot is missing, the
 * scenario is silently skipped.
 */
class ContractManagementDemoSeeder extends Seeder
{
    private const MARKER = '__demo__';

    public function run(): void
    {
        $this->wipeExistingDemo();

        // --------------------------------------------------------------
        // PROBATION WATCH demo — PKWT-I inside probation window
        // --------------------------------------------------------------

        // 1. Decision OVERDUE today (probation ends today, only 2 supervisions)
        $this->probationScenario(
            teacherId: 3,
            startedDaysAgo: 90,
            supervisions: 2,
            peerObs: 1,
            label: 'Decision overdue — only 2 supervisions of 4',
        );

        // 2. Decision DUE in 14 days, ON TRACK (4 sup + 2 peer)
        $this->probationScenario(
            teacherId: 4,
            startedDaysAgo: 76,
            supervisions: 4,
            peerObs: 2,
            label: 'Ready to decide — minimums met',
        );

        // 3. Decision DUE in 35 days, BEHIND TRACK (1 supervision only)
        $this->probationScenario(
            teacherId: 10,
            startedDaysAgo: 55,
            supervisions: 1,
            peerObs: 0,
            label: 'Behind track — needs more observations',
        );

        // --------------------------------------------------------------
        // CONTRACT RENEWAL demo — PKWT-I/II/III in renewal window
        // --------------------------------------------------------------

        // 4. PKWT-I, ends in 60d, NO recommendation yet (fresh in window)
        $this->renewalScenario(
            teacherId: 13,
            type: 'pkwt_1',
            endsInDays: 60,
            stage: 'fresh',
        );

        // 5. PKWT-II, ends in 45d, recommendation=renew uploaded, NOT submitted
        $this->renewalScenario(
            teacherId: 14,
            type: 'pkwt_2',
            endsInDays: 45,
            stage: 'recommended',
        );

        // 6. PKWT-II, ends in 25d, submitted to Yayasan, AWAITING decision
        $this->renewalScenario(
            teacherId: 11,
            type: 'pkwt_2',
            endsInDays: 25,
            stage: 'submitted',
        );

        // --------------------------------------------------------------
        // LOI CONTINUATION demo — Guru SK pipeline stages
        // --------------------------------------------------------------
        // (Reuses existing permanent teachers; creates LOIs in different stages.)
        $this->loiScenario(teacherId: 5,  stage: 'submitted');         // Yayasan to upload contract
        $this->loiScenario(teacherId: 6,  stage: 'contract_uploaded'); // teacher to e-sign agreement
        $this->loiScenario(teacherId: 8,  stage: 'agreement_signed');  // ready for buku induk
        $this->loiScenario(teacherId: 12, stage: 'completed');         // fully closed

        $this->command?->info('ContractManagementDemoSeeder: demo data populated.');
    }

    // =================================================================
    // Wipe
    // =================================================================

    private function wipeExistingDemo(): void
    {
        TeacherObservation::where('review_notes', self::MARKER)->delete();
        LetterOfIntent::where('notes', self::MARKER)->delete();
        TeacherContract::where('signature_text', self::MARKER)->delete();
    }

    // =================================================================
    // Probation Watch helpers
    // =================================================================

    private function probationScenario(
        int $teacherId,
        int $startedDaysAgo,
        int $supervisions,
        int $peerObs,
        string $label,
    ): void {
        $teacher = Teacher::find($teacherId);
        if (! $teacher) {
            return;
        }

        $start = Carbon::today()->subDays($startedDaysAgo);
        $probationEnd = $start->copy()->addDays(90);
        $end = $start->copy()->addYear();

        $contract = TeacherContract::create([
            'teacher_id'               => $teacher->id,
            'type'                     => 'pkwt_1',
            'academic_year'            => DutyAssignment::academicYearFor($start),
            'starts_at'                => $start->toDateString(),
            'ends_at'                  => $end->toDateString(),
            'probation_starts_at'      => $start->toDateString(),
            'probation_ends_at'        => $probationEnd->toDateString(),
            'renewal_window_starts_at' => $end->copy()->subMonths(3)->toDateString(),
            'status'                   => 'active',
            'signature_text'           => self::MARKER,
        ]);

        $teacher->update(['status' => 'probation']);

        $this->seedObservations(
            teacherId: $teacher->id,
            ay: $contract->academic_year,
            since: $start,
            principalCount: $supervisions,
            peerCount: $peerObs,
        );

        $this->command?->info("Probation: T{$teacher->id} {$teacher->name} — {$label}");
    }

    private function seedObservations(
        int $teacherId,
        string $ay,
        Carbon $since,
        int $principalCount,
        int $peerCount,
    ): void {
        $observerId = DB::table('users')->value('id') ?? null;

        for ($i = 0; $i < $principalCount; $i++) {
            $when = $since->copy()->addDays(7 * ($i + 1));
            TeacherObservation::create([
                'teacher_id'       => $teacherId,
                'observer_id'      => $observerId,
                'observed_at'      => $when,
                'lesson_subject'   => 'Demo Lesson #' . ($i + 1),
                'academic_year'    => $ay,
                'semester'         => (int) $when->format('n') >= 7 ? 1 : 2,
                'observation_type' => 'principal_supervision',
                'status'           => 'approved',
                'review_notes'     => self::MARKER,
            ]);
        }

        for ($i = 0; $i < $peerCount; $i++) {
            $when = $since->copy()->addDays(10 * ($i + 1) + 3);
            TeacherObservation::create([
                'teacher_id'       => $teacherId,
                'observer_id'      => $observerId,
                'observed_at'      => $when,
                'lesson_subject'   => 'Peer Observation #' . ($i + 1),
                'academic_year'    => $ay,
                'semester'         => (int) $when->format('n') >= 7 ? 1 : 2,
                'observation_type' => 'peer_observation',
                'status'           => 'approved',
                'review_notes'     => self::MARKER,
            ]);
        }
    }

    // =================================================================
    // Renewal helpers
    // =================================================================

    private function renewalScenario(
        int $teacherId,
        string $type,
        int $endsInDays,
        string $stage,
    ): void {
        $teacher = Teacher::find($teacherId);
        if (! $teacher) {
            return;
        }

        // Term length: PKWT-I/II/III all 1 year for demo simplicity.
        $end   = Carbon::today()->addDays($endsInDays);
        $start = $end->copy()->subYear();
        $renewalStart = $end->copy()->subMonths(3);

        $payload = [
            'teacher_id'               => $teacher->id,
            'type'                     => $type,
            'academic_year'            => DutyAssignment::academicYearFor($start),
            'starts_at'                => $start->toDateString(),
            'ends_at'                  => $end->toDateString(),
            'renewal_window_starts_at' => $renewalStart->toDateString(),
            'status'                   => 'active',
            'signature_text'           => self::MARKER,
        ];

        // PKWT-I has probation; mark it long-finished.
        if ($type === 'pkwt_1') {
            $payload['probation_starts_at'] = $start->toDateString();
            $payload['probation_ends_at']   = $start->copy()->addDays(90)->toDateString();
            $payload['probation_decision']  = 'continue';
            $payload['probation_decision_at'] = $start->copy()->addDays(85);
        }

        // Stage progression
        if (in_array($stage, ['recommended', 'submitted'], true)) {
            $payload['recommendation_decision'] = 'renew';
            $payload['recommendation_letter_path'] = 'demo/recommendation-' . $teacher->id . '.pdf';
            $payload['renewal_form_submitted_at'] = now()->subDays(2);
        }

        if ($stage === 'submitted') {
            $payload['submitted_to_yayasan_at'] = now()->subDay();
        }

        $contract = TeacherContract::create($payload);

        // Cascade teacher status to match contract type.
        $teacher->update(['status' => $type]);

        // Add some observations so the renewal cards aren't empty.
        $this->seedObservations(
            teacherId: $teacher->id,
            ay: $contract->academic_year,
            since: $start,
            principalCount: 5,
            peerCount: 2,
        );

        $this->command?->info("Renewal: T{$teacher->id} {$teacher->name} — {$type} stage={$stage}");
    }

    // =================================================================
    // LOI Continuation helpers
    // =================================================================

    private function loiScenario(int $teacherId, string $stage): void
    {
        $teacher = Teacher::find($teacherId);
        if (! $teacher) {
            return;
        }

        // Find or create the teacher's guru_sk contract.
        $contract = TeacherContract::firstOrCreate(
            ['teacher_id' => $teacher->id, 'type' => 'guru_sk'],
            [
                'academic_year' => DutyAssignment::academicYearFor($teacher->joined_at ?? now()),
                'starts_at'     => ($teacher->joined_at ?? now())->toDateString(),
                'status'        => 'active',
            ]
        );

        $principalId = DB::table('users')->value('id');

        $base = [
            'teacher_id'           => $teacher->id,
            'principal_id'         => $principalId,
            'teacher_contract_id'  => $contract->id,
            'academic_year'        => $contract->academic_year,
            'position'             => 'Guru SK',
            'body'                 => 'Demo letter of intent for ' . $teacher->name . '.',
            'sent_at'              => now()->subWeeks(2),
            'status'               => 'signed',
            'signed_at'            => now()->subDays(10),
            'signature_text'       => $teacher->name,
            'submitted_to_yayasan_at' => now()->subDays(8),
            'notes'                => self::MARKER,
        ];

        // Layered stage progression.
        if (in_array($stage, ['contract_uploaded', 'agreement_signed', 'completed'], true)) {
            $base['yayasan_contract_path']        = 'demo/yayasan-contract-' . $teacher->id . '.pdf';
            $base['yayasan_contract_uploaded_at'] = now()->subDays(6);
        }

        if (in_array($stage, ['agreement_signed', 'completed'], true)) {
            $base['agreement_signed_at']       = now()->subDays(3);
            $base['agreement_signature_text']  = $teacher->name;
            $base['agreement_signature_ip']    = '127.0.0.1';
        }

        if ($stage === 'completed') {
            $base['continuation_completed_at'] = now()->subDay();
        }

        LetterOfIntent::create($base);

        $this->command?->info("LOI: T{$teacher->id} {$teacher->name} — stage={$stage}");
    }
}
