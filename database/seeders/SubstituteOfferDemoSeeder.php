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
     * Idempotent: wipes substitute_offers, resets auto_search_* fields,
     * then re-seeds a variety of states so the principal demo shows
     * Searching / Interested-needs-pick / Closed-full / Disabled / Assigned.
     */
    public function run(): void
    {
        SubstituteOffer::query()->delete();
        TeacherLeave::query()->update([
            'auto_search_enabled'   => true,
            'auto_search_status'    => 'disabled',
            'auto_search_opened_at' => null,
            'auto_search_closes_at' => null,
            'auto_search_closed_at' => null,
            'auto_search_cap'       => 5,
        ]);

        $pending = TeacherLeave::where('status', 'pending')
            ->whereNull('substitute_teacher_id')
            ->orderByDesc('id')
            ->take(6)
            ->get();

        if ($pending->isEmpty()) {
            return;
        }

        // [scenario_name, search_status, [status, status, status, ...] up to 5]
        $scenarios = [
            ['searching',        'open',            ['pending', 'pending', 'pending', 'pending', 'pending']],
            ['needs-pick',       'open',            ['interested', 'interested', 'pending', 'declined', 'pending']],
            ['closed-full',      'closed_full',     ['interested', 'interested', 'interested', 'interested', 'interested']],
            ['mixed-open',       'open',            ['interested', 'pending', 'declined']],
            ['closed-manual',    'closed_manual',   ['declined', 'declined', 'cancelled']],
            ['disabled',         'disabled',        []],
        ];

        foreach ($pending as $i => $leave) {
            $scenario = $scenarios[$i] ?? $scenarios[0];
            [$name, $searchStatus, $statuses] = $scenario;

            if ($searchStatus === 'disabled') {
                $leave->update(['auto_search_enabled' => false, 'auto_search_status' => 'disabled']);
                $this->command?->info("  Leave #{$leave->id}: {$name}");
                continue;
            }

            $candidates = SubstituteSuggester::for($leave, 15)
                ->filter(fn ($c) => $c['is_assignable'])
                ->take(count($statuses));

            if ($candidates->isEmpty()) {
                continue;
            }

            $openedAt  = now()->subHours(rand(2, 18));
            $closesAt  = $openedAt->copy()->addHours(24);
            $closedAt  = in_array($searchStatus, ['closed_full', 'closed_manual'], true)
                ? now()->subMinutes(rand(15, 180)) : null;

            $leave->update([
                'auto_search_enabled'   => true,
                'auto_search_status'    => $searchStatus,
                'auto_search_opened_at' => $openedAt,
                'auto_search_closes_at' => $closesAt,
                'auto_search_closed_at' => $closedAt,
                'auto_search_cap'       => 5,
            ]);

            foreach ($candidates as $idx => $candidate) {
                $status = $statuses[$idx] ?? 'pending';
                $sentAt = $openedAt->copy()->addMinutes(rand(0, 10));

                SubstituteOffer::create([
                    'teacher_leave_id' => $leave->id,
                    'teacher_id'       => $candidate['teacher']->id,
                    'token'            => SubstituteOffer::generateToken(),
                    'status'           => $status,
                    'sent_at'          => $sentAt,
                    'expires_at'       => $closesAt,
                    'responded_at'     => in_array($status, ['interested', 'declined', 'cancelled'], true)
                        ? $sentAt->copy()->addMinutes(rand(15, 240)) : null,
                    'round'            => 1,
                ]);
            }

            $this->command?->info("  Leave #{$leave->id}: {$name} ({$searchStatus})");
        }
    }
}
