<?php

namespace Database\Seeders;

use App\Models\SubstituteOffer;
use App\Models\Teacher;
use App\Models\TeacherLeave;
use App\Support\SubstituteSuggester;
use Illuminate\Database\Seeder;

class SubstituteOfferDemoSeeder extends Seeder
{
    /**
     * Idempotent: wipes substitute_offers, re-seeds a mix of broadcast states
     * across the most recent pending leaves so the principal demo shows
     * Pending / Accepted / Declined cases side by side.
     */
    public function run(): void
    {
        SubstituteOffer::query()->delete();

        $pending = TeacherLeave::where('status', 'pending')
            ->whereNull('substitute_teacher_id')
            ->orderByDesc('id')
            ->take(6)
            ->get();

        if ($pending->isEmpty()) {
            return;
        }

        $scenarios = [
            // [scenario_name, status_for_each_of_3_offers]
            ['broadcasting', ['pending', 'pending', 'pending']],
            ['mixed-response', ['accepted', 'declined', 'pending']],
            ['all-declined', ['declined', 'declined', 'declined']],
            ['fresh-broadcast', ['pending', 'pending', 'pending']],
            ['one-declined', ['pending', 'declined', 'pending']],
            ['expired', ['expired', 'expired', 'expired']],
        ];

        foreach ($pending as $i => $leave) {
            $scenario = $scenarios[$i] ?? $scenarios[0];
            [$name, $statuses] = $scenario;

            $candidates = SubstituteSuggester::for($leave, 10)
                ->filter(fn ($c) => $c['is_assignable'])
                ->take(3);

            if ($candidates->isEmpty()) {
                continue;
            }

            foreach ($candidates as $idx => $candidate) {
                $status = $statuses[$idx] ?? 'pending';
                $sentAt = now()->subHours(rand(1, 6));

                $offer = SubstituteOffer::create([
                    'teacher_leave_id' => $leave->id,
                    'teacher_id'       => $candidate['teacher']->id,
                    'token'            => SubstituteOffer::generateToken(),
                    'status'           => $status,
                    'sent_at'          => $sentAt,
                    'expires_at'       => $sentAt->copy()->addHours(4),
                    'responded_at'     => in_array($status, ['accepted', 'declined']) ? $sentAt->copy()->addMinutes(rand(10, 90)) : null,
                    'round'            => 1,
                ]);

                if ($status === 'accepted') {
                    $leave->update(['substitute_teacher_id' => $candidate['teacher']->id]);
                }
            }

            $this->command?->info("  Seeded offers for leave #{$leave->id} (scenario: {$name})");
        }
    }
}
