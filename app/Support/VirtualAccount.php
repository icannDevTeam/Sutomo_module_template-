<?php

namespace App\Support;

/**
 * Yayasan Sutomo Virtual Account scheme (BCA).
 *
 * Pattern: <BANK_PREFIX:5><unit_digit:1><purpose_digit:1><ref:n>
 *   e.g. 14339 1 4 2610011 → SMA enrollment fee for registration #2610011
 *        14339 3 0 2024001  → SD school tuition for NIS 2024001
 *
 * Unit digit lookup
 *   1 SMA · 2 SMP · 3 SD · 4 TK · 5 Playgroup · 6 Pre-Nursery
 *
 * Purpose digit lookup
 *   0 School tuition · 1 Administration fee · 2 Textbook purchase
 *   3 School development fee · 4 Enrollment fee
 *
 * The actual <ref> tail (registration number for new applicants, NIS for
 * existing students) is decided by the caller — this helper only composes,
 * validates and parses the canonical string.
 */
class VirtualAccount
{
    public const BANK_PREFIX = '14339';

    public const UNIT_DIGITS = [
        'sma'         => '1',
        'smp'         => '2',
        'sd'          => '3',
        'tk'          => '4',
        'playgroup'   => '5',
        'pre_nursery' => '6',
    ];

    public const PURPOSE_DIGITS = [
        'tuition'    => '0',
        'admin'      => '1',
        'books'      => '2',
        'dev_fee'    => '3',
        'enrollment' => '4',
    ];

    public const PURPOSE_LABELS = [
        'tuition'    => 'School Tuition',
        'admin'      => 'Administration Fee',
        'books'      => 'Textbook Purchase',
        'dev_fee'    => 'School Development Fee',
        'enrollment' => 'Enrollment Fee',
    ];

    public static function unitDigit(?string $unit): ?string
    {
        return self::UNIT_DIGITS[$unit] ?? null;
    }

    public static function purposeDigit(?string $purpose): ?string
    {
        return self::PURPOSE_DIGITS[$purpose] ?? null;
    }

    /**
     * Build the canonical VA for a given (unit, purpose, ref).
     * Returns null when the unit or purpose is unknown so callers can fall
     * back to "issued externally" instead of crashing on bad input.
     */
    public static function compose(?string $unit, string $purpose, string $ref): ?string
    {
        $u = self::unitDigit($unit);
        $p = self::purposeDigit($purpose);
        if ($u === null || $p === null) return null;

        $ref = preg_replace('/\D+/', '', $ref) ?? '';
        if ($ref === '') return null;

        return self::BANK_PREFIX . $u . $p . $ref;
    }

    /**
     * Parse a VA back into its parts. Returns null on malformed input so
     * UI code can surface "VA number is not a recognised Sutomo VA".
     *
     * @return array{bank:string, unit_digit:string, purpose_digit:string, ref:string, unit:?string, purpose:?string}|null
     */
    public static function parse(?string $va): ?array
    {
        if (! $va) return null;
        $digits = preg_replace('/\D+/', '', $va);
        if (! $digits || strlen($digits) < 8) return null;
        if (! str_starts_with($digits, self::BANK_PREFIX)) return null;

        $tail = substr($digits, strlen(self::BANK_PREFIX));
        if (strlen($tail) < 3) return null;

        $unitDigit    = $tail[0];
        $purposeDigit = $tail[1];
        $ref          = substr($tail, 2);

        return [
            'bank'          => self::BANK_PREFIX,
            'unit_digit'    => $unitDigit,
            'purpose_digit' => $purposeDigit,
            'ref'           => $ref,
            'unit'          => array_search($unitDigit, self::UNIT_DIGITS, true) ?: null,
            'purpose'       => array_search($purposeDigit, self::PURPOSE_DIGITS, true) ?: null,
        ];
    }

    /** Pretty-print a VA as `14339 1 4 2610011` (groups for human readability). */
    public static function format(?string $va): ?string
    {
        $parts = self::parse($va);
        if (! $parts) return $va;
        return $parts['bank'] . ' ' . $parts['unit_digit'] . ' ' . $parts['purpose_digit'] . ' ' . $parts['ref'];
    }
}
