<?php

namespace Database\Seeders;

use App\Models\Teacher;
use Illuminate\Database\Seeder;

class TeacherSubstitutionConfigDemoSeeder extends Seeder
{
    /**
     * Stamp a realistic spread of substitution categories so the
     * Substitution Config page and the auto-search bias have visible effect.
     *
     * Strategy:
     *   - Reset everyone to 'standard' first (idempotent).
     *   - Tag the TR-DEMO personas to match their Teacher Review archetypes.
     *   - Spray the rest of the active roster: ~5% VIP, ~10% Preferred,
     *     ~5% Restricted, ~3% Blocked, the rest Standard.
     */
    public function run(): void
    {
        // 1) Reset (idempotent)
        Teacher::query()->update([
            'substitution_category'        => 'standard',
            'substitution_category_note'   => null,
            'substitution_category_set_by' => null,
            'substitution_category_set_at' => null,
        ]);

        $now = now();

        // 2) TR-DEMO personas — line them up with their Teacher Review archetype
        $demoMap = [
            'TR-DEMO-01' => ['vip',        'Star performer — Principal trusts to cover any class.'],
            'TR-DEMO-02' => ['preferred',  'Steady developer — reliable cover, good ranking boost.'],
            'TR-DEMO-03' => ['restricted', 'On Watch — only invite when nobody else is available.'],
            'TR-DEMO-04' => ['standard',   null],
            'TR-DEMO-05' => ['blocked',    'On long leave — do not invite to substitute.'],
            'TR-DEMO-06' => ['restricted', 'Manual override flagged Not Ready — keep low priority.'],
        ];

        foreach ($demoMap as $code => [$category, $note]) {
            Teacher::query()
                ->where('code', $code)
                ->update([
                    'substitution_category'        => $category,
                    'substitution_category_note'   => $note,
                    'substitution_category_set_at' => $now,
                ]);
        }

        // 3) Spray across the rest of the active roster
        $rest = Teacher::query()
            ->where('status', '!=', 'alumni')
            ->where(function ($q) {
                $q->whereNull('code')->orWhere('code', 'NOT LIKE', 'TR-DEMO-%');
            })
            ->inRandomOrder()
            ->get();

        if ($rest->isEmpty()) {
            return;
        }

        $total = $rest->count();
        $vipCount        = max(2, (int) round($total * 0.05));
        $preferredCount  = max(3, (int) round($total * 0.10));
        $restrictedCount = max(2, (int) round($total * 0.05));
        $blockedCount    = max(1, (int) round($total * 0.03));

        $cursor = 0;
        $apply = function (string $cat, int $count, ?string $note) use ($rest, &$cursor, $now) {
            for ($i = 0; $i < $count && $cursor < $rest->count(); $i++, $cursor++) {
                $t = $rest[$cursor];
                $t->forceFill([
                    'substitution_category'        => $cat,
                    'substitution_category_note'   => $note,
                    'substitution_category_set_at' => $now,
                ])->save();
            }
        };

        $apply('vip',        $vipCount,        'Trusted favourite — always at the top of the candidate list.');
        $apply('preferred',  $preferredCount,  'Globally preferred — boosted above generic matches.');
        $apply('restricted', $restrictedCount, 'Last resort only.');
        $apply('blocked',    $blockedCount,    'Excluded from auto-search.');
        // Remainder stays 'standard'
    }
}
