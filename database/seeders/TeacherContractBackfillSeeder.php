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
 * Phase 1 backfill.
 *
 * 1. For every Teacher whose status is 'permanent' (legacy term for Guru SK)
 *    AND who has at least one LOI, create a `teacher_contracts` row of type
 *    `guru_sk` and link all of that teacher's LOIs to it.
 * 2. For every existing teacher_observation row missing academic_year/semester,
 *    derive them from observed_at (semester 1 = Jul-Dec, semester 2 = Jan-Jun).
 *
 * Idempotent: safe to re-run.
 */
class TeacherContractBackfillSeeder extends Seeder
{
    public function run(): void
    {
        $this->backfillGuruSkContracts();
        $this->backfillObservationAyAndSemester();
    }

    private function backfillGuruSkContracts(): void
    {
        $candidates = Teacher::query()
            ->whereIn('status', ['permanent', 'guru_sk'])
            ->whereHas('lettersOfIntent')
            ->with(['lettersOfIntent' => fn ($q) => $q->orderBy('created_at')])
            ->get();

        $created = 0;

        foreach ($candidates as $teacher) {
            // Skip if a guru_sk contract already exists for this teacher.
            $existing = TeacherContract::query()
                ->where('teacher_id', $teacher->id)
                ->where('type', 'guru_sk')
                ->first();

            if ($existing) {
                $contract = $existing;
            } else {
                $startsAt = $teacher->joined_at
                    ? Carbon::parse($teacher->joined_at)
                    : Carbon::parse($teacher->lettersOfIntent->first()->created_at);

                $contract = TeacherContract::create([
                    'teacher_id'     => $teacher->id,
                    'type'           => 'guru_sk',
                    'academic_year'  => DutyAssignment::academicYearFor($startsAt),
                    'starts_at'      => $startsAt->toDateString(),
                    'ends_at'        => null,
                    'status'         => 'active',
                ]);
                $created++;
            }

            // Link every LOI for this teacher to the guru_sk contract if not already linked.
            LetterOfIntent::query()
                ->where('teacher_id', $teacher->id)
                ->whereNull('teacher_contract_id')
                ->update(['teacher_contract_id' => $contract->id]);
        }

        $this->command?->info("TeacherContractBackfill: created {$created} guru_sk contracts.");
    }

    private function backfillObservationAyAndSemester(): void
    {
        $rows = TeacherObservation::query()
            ->whereNull('academic_year')
            ->orWhereNull('semester')
            ->select(['id', 'observed_at'])
            ->get();

        $updated = 0;

        foreach ($rows as $obs) {
            if (! $obs->observed_at) {
                continue;
            }
            $date = Carbon::parse($obs->observed_at);
            $ay = DutyAssignment::academicYearFor($date);
            $semester = (int) $date->format('n') >= 7 ? 1 : 2;

            DB::table('teacher_observations')
                ->where('id', $obs->id)
                ->update([
                    'academic_year' => $ay,
                    'semester'      => $semester,
                ]);
            $updated++;
        }

        $this->command?->info("TeacherContractBackfill: backfilled AY/semester on {$updated} observations.");
    }
}
