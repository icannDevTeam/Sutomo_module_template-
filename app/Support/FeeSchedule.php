<?php

namespace App\Support;

use App\Models\Application;

/**
 * Sutomo fee schedule (derived from Feessss.html).
 *
 * Matrix:
 *   Campus → Track → Unit → applicant_type → [fee_key => amount]
 *
 * Demo amounts are illustrative — Finance will own the real numbers.
 * Rules from the spreadsheet:
 *   - Sutomo 1 has 3 tracks (Regular Normal, Regular Unggulan, Plus); Sutomo 2 has only Regular Normal.
 *   - New Student pays ALL fees.
 *   - Existing Student (Same Unit) pays NOTHING (already in system).
 *   - Existing Student (Diff Unit) pays only if track upgrade requires it (e.g. SD Normal → SMP Plus = pay as new).
 *   - Teacher kids: 50% discount on every applicable fee.
 */
class FeeSchedule
{
    /** All recognised tracks. */
    public const TRACKS = [
        'normal'    => 'Regular (Normal)',
        'unggulan'  => 'Regular (Unggulan)',
        'plus'      => 'Plus',
    ];

    /** Tracks available per unit (Sutomo 1 default). */
    public const UNIT_TRACKS = [
        'pre_nursery' => ['normal', 'plus'],
        'playgroup'   => ['normal', 'plus'],
        'tk'          => ['normal', 'plus'],
        'sd'          => ['normal', 'plus'],
        'smp'         => ['normal', 'unggulan', 'plus'],
        'sma'         => ['normal', 'unggulan', 'plus'],
    ];

    /** Demo amounts (IDR) — Finance owns real numbers later. */
    public const TRACK_FEES = [
        'normal'   => [
            'registration' => 300_000,
            'development'  => 5_000_000,
            'commitment'   => 2_500_000,
            'books'        => 1_500_000,
            'tuition'      => 800_000,
            'admin'        => 200_000,
        ],
        'unggulan' => [
            'registration' => 300_000,
            'development'  => 8_000_000,
            'commitment'   => 4_000_000,
            'books'        => 2_000_000,
            'tuition'      => 1_200_000,
            'admin'        => 250_000,
        ],
        'plus'     => [
            'registration' => 300_000,
            'development'  => 12_000_000,
            'commitment'   => 6_000_000,
            'books'        => 2_500_000,
            'tuition'      => 1_800_000,
            'admin'        => 300_000,
        ],
    ];

    public const FEE_LABELS = [
        'registration' => 'Registration Fee',
        'development'  => 'Development Fee',
        'commitment'   => 'Commitment Fee',
        'books'        => 'Book Fee',
        'tuition'      => 'Tuition (monthly)',
        'admin'        => 'Admin Fee',
    ];

    /** Discount when applicant is a Sutomo teacher's child. */
    public const TEACHER_CHILD_DISCOUNT = 0.50;

    /**
     * Compute a single fee for an application.
     */
    public static function amountFor(Application $a, string $feeKey, ?string $track = null): int
    {
        $track    = $track ?: self::resolveTrack($a);
        $isNew    = $a->applicant_type === 'new';
        $sameUnit = $a->applicant_type !== 'new'
            && filled($a->existing_unit)
            && strtolower($a->existing_unit) === strtolower($a->campus ?? '');

        // Existing student same unit → no charge
        if (! $isNew && $sameUnit) {
            return 0;
        }

        $base = self::TRACK_FEES[$track][$feeKey] ?? 0;

        if ($a->is_teacher_child) {
            $base = (int) round($base * (1 - self::TEACHER_CHILD_DISCOUNT));
        }

        return $base;
    }

    /**
     * Full fee summary for the dossier / dev-fee modal.
     *
     * @return array<string, array{label:string, amount:int, payable:bool}>
     */
    public static function summary(Application $a, ?string $track = null): array
    {
        $track = $track ?: self::resolveTrack($a);
        $out = [];
        foreach (self::FEE_LABELS as $key => $label) {
            $amt = self::amountFor($a, $key, $track);
            $out[$key] = [
                'label'   => $label,
                'amount'  => $amt,
                'payable' => $amt > 0,
            ];
        }
        return $out;
    }

    /** Best-guess track from stored data; defaults to Regular Normal. */
    public static function resolveTrack(Application $a): string
    {
        $stored = data_get($a->meta, 'track');
        if (is_string($stored) && array_key_exists($stored, self::TRACK_FEES)) {
            return $stored;
        }
        return 'normal';
    }

    /** Pretty money format (Rp 1.500.000). */
    public static function rp(int $n): string
    {
        return 'Rp ' . number_format($n, 0, ',', '.');
    }
}
