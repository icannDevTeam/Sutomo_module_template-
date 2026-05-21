<?php

namespace App\Services\Timetable;

use App\Models\SubjectRequirement;
use App\Models\TeacherLoad;
use App\Models\TimetableLesson;
use Illuminate\Support\Collection;

/**
 * Backtracking solver that fills a timetable to satisfy SubjectRequirements
 * while respecting:
 *   - existing locked lessons (kept untouched)
 *   - teacher cap (weekly_cap)
 *   - teacher subject whitelist (TeacherLoad.subjects)
 *   - teacher unavailability (TeacherLoad.unavailable_slots)
 *   - no teacher double-book / no class double-book
 *
 * Usage:
 *   $svc->generate($definitionId, $teachersConfig);
 *
 * $teachersConfig: id => [
 *     'level'   => 'smp' | 'sd',
 *     'subject' => 'Biologi',          // primary display
 *     'classes' => ['P VII-01', ...],  // classes this teacher may serve
 * ]
 *
 * Returns:
 *   [
 *     'placed'   => int,
 *     'kept'     => int,                // locked / pre-existing lessons untouched
 *     'cleared'  => int,                // unlocked lessons wiped before solving
 *     'unsolved' => [ ['class'=>..., 'subject'=>..., 'remaining'=>n], ... ],
 *   ]
 */
class AutoGenerator
{
    /** Hard time cap so the page never hangs on a pathological input. */
    private const MAX_STEPS = 200_000;

    public function generate(int $definitionId, array $teachersConfig, array $periods): array
    {
        // 1. Wipe unlocked lessons, keep locked.
        $cleared = TimetableLesson::where('definition_id', $definitionId)
            ->where('locked', false)->count();
        TimetableLesson::where('definition_id', $definitionId)
            ->where('locked', false)->delete();

        $locked = TimetableLesson::where('definition_id', $definitionId)
            ->where('locked', true)->get();

        // 2. Build slot universe: per level, list of (session, period, day) skipping breaks.
        $slotsByLevel = [];
        foreach (['smp', 'sd'] as $level) {
            $defs = $level === 'sd'
                ? \App\Filament\Principal\Pages\TimetableBuilder::PERIODS_SD
                : \App\Filament\Principal\Pages\TimetableBuilder::PERIODS_SMP;
            $slots = [];
            foreach ($defs as $session => $rows) {
                foreach ($rows as $row) {
                    if (! empty($row['break'])) continue;
                    foreach (\App\Filament\Principal\Pages\TimetableBuilder::DAYS as $day) {
                        $slots[] = [
                            'session' => $session,
                            'period'  => $row['no'],
                            'day'     => $day,
                        ];
                    }
                }
            }
            $slotsByLevel[$level] = $slots;
        }

        // 3. Load teacher loads (caps + subjects + unavailable).
        $loads = TeacherLoad::where('definition_id', $definitionId)->get()->keyBy('teacher_ref');

        // 4. Resource maps from locked.
        $teacherBusy = [];   // teacherId => slotKey => true
        $classBusy   = [];   // class => slotKey => true
        $teacherLoad = [];   // teacherId => count
        foreach ($loads as $l) {
            $teacherLoad[$l->teacher_ref] = 0;
        }
        foreach ($locked as $l) {
            $sk = $l->session . '|' . $l->period . '|' . $l->day;
            $teacherBusy[$l->teacher_ref][$sk] = true;
            $classBusy[$l->class_code][$sk]    = true;
            $teacherLoad[$l->teacher_ref] = ($teacherLoad[$l->teacher_ref] ?? 0) + 1;
        }

        // 5. Build demand list from requirements.
        // Each demand = one lesson to schedule: ['class' => 'P VII-01', 'subject' => 'BIOLOGI', 'grade' => '7']
        $reqs = SubjectRequirement::where('definition_id', $definitionId)->get();
        $classCodes = collect($teachersConfig)->pluck('classes')->flatten()->unique();

        $demands = [];
        foreach ($reqs as $req) {
            foreach ($classCodes as $code) {
                if ($this->extractGrade($code) !== (string) $req->grade_level) continue;
                // Subtract locked occurrences already in place.
                $already = $locked->where('class_code', $code)->where('subject', $req->subject)->count();
                $needed  = max(0, ((int) $req->weekly_hours) - $already);
                for ($i = 0; $i < $needed; $i++) {
                    $demands[] = [
                        'class'   => $code,
                        'subject' => strtoupper($req->subject),
                        'grade'   => (string) $req->grade_level,
                    ];
                }
            }
        }

        // 6. Pre-compute candidate teachers + candidate slots for each demand → enables MRV ordering.
        foreach ($demands as $i => $d) {
            $cands = [];
            foreach ($teachersConfig as $tid => $cfg) {
                $load = $loads->get($tid);
                if ($load && ! empty($load->subjects) && ! in_array($d['subject'], array_map('strtoupper', $load->subjects), true)) continue;
                if (! in_array($d['class'], $cfg['classes'] ?? [], true)) continue;
                $cands[] = $tid;
            }
            $demands[$i]['teacher_candidates'] = $cands;
            $demands[$i]['slot_count'] = isset($cands[0])
                ? count($slotsByLevel[$teachersConfig[$cands[0]]['level']] ?? [])
                : 0;
        }

        // 7. MRV-style ordering: lessons with fewest teacher candidates first, then by class.
        usort($demands, function ($a, $b) {
            $ac = count($a['teacher_candidates']);
            $bc = count($b['teacher_candidates']);
            if ($ac !== $bc) return $ac <=> $bc;
            return strcmp($a['class'], $b['class']);
        });

        // 8. Backtrack.
        $placed = [];
        $unsolved = [];
        $steps = 0;

        $solve = function (int $idx) use (
            &$solve, &$demands, &$placed, &$teacherBusy, &$classBusy, &$teacherLoad,
            &$slotsByLevel, &$teachersConfig, &$loads, &$steps
        ): bool {
            if ($steps++ > self::MAX_STEPS) return false;
            if ($idx >= count($demands)) return true;

            $d = $demands[$idx];
            if (empty($d['teacher_candidates'])) return false; // skip handled below

            foreach ($d['teacher_candidates'] as $tid) {
                $cfg  = $teachersConfig[$tid];
                $load = $loads->get($tid);
                $cap  = $load?->weekly_cap ?? 99;
                if (($teacherLoad[$tid] ?? 0) >= $cap) continue;

                $unavail = collect($load?->unavailable_slots ?? [])
                    ->map(fn ($u) => ($u['session'] ?? '') . '|' . ($u['period'] ?? '') . '|' . ($u['day'] ?? ''))
                    ->all();

                // Try each slot for this teacher's level, shuffled lightly for variety.
                $slots = $slotsByLevel[$cfg['level']] ?? [];
                // Mild randomization so re-runs aren't identical, but deterministic per demand idx.
                usort($slots, fn ($a, $b) => crc32($a['day'] . $a['period'] . $idx) <=> crc32($b['day'] . $b['period'] . $idx));

                foreach ($slots as $slot) {
                    $sk = $slot['session'] . '|' . $slot['period'] . '|' . $slot['day'];
                    if (in_array($sk, $unavail, true)) continue;
                    if (! empty($teacherBusy[$tid][$sk])) continue;
                    if (! empty($classBusy[$d['class']][$sk])) continue;

                    // Place tentatively.
                    $teacherBusy[$tid][$sk]      = true;
                    $classBusy[$d['class']][$sk] = true;
                    $teacherLoad[$tid] = ($teacherLoad[$tid] ?? 0) + 1;
                    $placed[] = [
                        'teacher' => $tid,
                        'class'   => $d['class'],
                        'subject' => $d['subject'],
                        'session' => $slot['session'],
                        'period'  => $slot['period'],
                        'day'     => $slot['day'],
                    ];

                    if ($solve($idx + 1)) return true;

                    // Rollback.
                    array_pop($placed);
                    unset($teacherBusy[$tid][$sk], $classBusy[$d['class']][$sk]);
                    $teacherLoad[$tid]--;
                }
            }
            return false;
        };

        $solved = $solve(0);

        // 9. If full backtracking failed (or hit step cap), accept partial = whatever's in $placed,
        //    list remaining demands as unsolved (aggregated by class+subject).
        if (! $solved) {
            // partial-greedy fallback for whatever didn't get placed
            $placedIdx = count($placed);
            for ($i = $placedIdx; $i < count($demands); $i++) {
                $d = $demands[$i];
                $found = false;
                foreach ($d['teacher_candidates'] as $tid) {
                    $cfg  = $teachersConfig[$tid];
                    $load = $loads->get($tid);
                    $cap  = $load?->weekly_cap ?? 99;
                    if (($teacherLoad[$tid] ?? 0) >= $cap) continue;
                    $unavail = collect($load?->unavailable_slots ?? [])
                        ->map(fn ($u) => ($u['session'] ?? '') . '|' . ($u['period'] ?? '') . '|' . ($u['day'] ?? ''))
                        ->all();
                    foreach ($slotsByLevel[$cfg['level']] ?? [] as $slot) {
                        $sk = $slot['session'] . '|' . $slot['period'] . '|' . $slot['day'];
                        if (in_array($sk, $unavail, true)) continue;
                        if (! empty($teacherBusy[$tid][$sk])) continue;
                        if (! empty($classBusy[$d['class']][$sk])) continue;
                        $teacherBusy[$tid][$sk]      = true;
                        $classBusy[$d['class']][$sk] = true;
                        $teacherLoad[$tid] = ($teacherLoad[$tid] ?? 0) + 1;
                        $placed[] = [
                            'teacher' => $tid,
                            'class'   => $d['class'],
                            'subject' => $d['subject'],
                            'session' => $slot['session'],
                            'period'  => $slot['period'],
                            'day'     => $slot['day'],
                        ];
                        $found = true;
                        break 2;
                    }
                }
                if (! $found) {
                    $unsolved[] = ['class' => $d['class'], 'subject' => $d['subject']];
                }
            }
        }

        // 10. Persist all placed.
        foreach ($placed as $p) {
            TimetableLesson::create([
                'definition_id' => $definitionId,
                'teacher_ref'   => $p['teacher'],
                'class_code'    => $p['class'],
                'subject'       => $p['subject'],
                'session'       => $p['session'],
                'period'        => $p['period'],
                'day'           => $p['day'],
            ]);
        }

        // 11. Recompute conflicts on the whole sheet.
        $svc = app(ConflictService::class);
        TimetableLesson::where('definition_id', $definitionId)
            ->get()
            ->each(fn ($l) => $svc->checkAndStore($l));

        // Aggregate unsolved by class+subject for friendlier output
        $aggUnsolved = collect($unsolved)
            ->groupBy(fn ($u) => $u['class'] . '|' . $u['subject'])
            ->map(fn ($g) => [
                'class'     => $g->first()['class'],
                'subject'   => $g->first()['subject'],
                'remaining' => $g->count(),
            ])
            ->values()
            ->all();

        return [
            'placed'   => count($placed),
            'kept'     => $locked->count(),
            'cleared'  => $cleared,
            'unsolved' => $aggUnsolved,
        ];
    }

    private function extractGrade(string $classCode): ?string
    {
        if (preg_match('/\b(VII|VIII|IX|X|XI|XII|IV|V|VI|I{1,3})\b/u', $classCode, $m)) {
            return (string) $this->romanToInt($m[1]);
        }
        if (preg_match('/^(\d{1,2})\b/', $classCode, $m)) {
            return $m[1];
        }
        return null;
    }

    private function romanToInt(string $r): int
    {
        $map = ['I' => 1, 'V' => 5, 'X' => 10, 'L' => 50, 'C' => 100];
        $result = 0; $prev = 0;
        for ($i = strlen($r) - 1; $i >= 0; $i--) {
            $v = $map[$r[$i]] ?? 0;
            $result += $v < $prev ? -$v : $v;
            $prev = $v;
        }
        return $result;
    }
}
