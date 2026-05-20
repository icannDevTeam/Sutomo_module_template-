<?php

namespace App\Support;

/**
 * Canonical Sutomo school directory.
 *
 * Hierarchy (from the demo spec):
 *
 *   School  (Yayasan-managed)   → SCH-001 Sutomo 1, SCH-002 Sutomo 2
 *     └── Campus (Principal)    → CMP-001 Thamrin / CMP-002 Bintang (Sutomo 1)
 *                                 CMP-003 Brayan (Sutomo 2)
 *           └── Unit (Unit Head) → UNT-001..006 (Pre-Nursery → SMA)
 *
 * Anywhere demo data is rendered or seeded, source the school / campus / unit
 * names from here so the Principal sees their own org during the live demo.
 */
class SchoolDirectory
{
    /** Schools managed by the Yayasan. */
    public const SCHOOLS = [
        'SCH-001' => 'Sutomo 1',
        'SCH-002' => 'Sutomo 2',
    ];

    /**
     * Physical campuses. Each belongs to exactly one school and is run by a Principal.
     * @var array<string, array{name:string, school:string}>
     */
    public const CAMPUSES = [
        'CMP-001' => ['name' => 'Thamrin', 'school' => 'SCH-001'],
        'CMP-002' => ['name' => 'Bintang', 'school' => 'SCH-001'],
        'CMP-003' => ['name' => 'Brayan',  'school' => 'SCH-002'],
    ];

    /**
     * Educational units run by a Unit Head. The slug (right column) is the
     * legacy `unit` value already used by FeeSchedule and Application records.
     * @var array<string, array{code:string, label:string}>
     */
    public const UNITS = [
        'pre_nursery' => ['code' => 'UNT-001', 'label' => 'Pre-Nursery'],
        'playgroup'   => ['code' => 'UNT-002', 'label' => 'Playgroup'],
        'tk'          => ['code' => 'UNT-003', 'label' => 'TK'],
        'sd'          => ['code' => 'UNT-004', 'label' => 'SD'],
        'smp'         => ['code' => 'UNT-005', 'label' => 'SMP'],
        'sma'         => ['code' => 'UNT-006', 'label' => 'SMA'],
    ];

    /** Who manages each layer (drives policy / approval routing). */
    public const MANAGED_BY = [
        'school' => 'Yayasan',
        'campus' => 'Principal',
        'unit'   => 'Unit Head',
    ];

    /** Applicant type taxonomy used across admissions. */
    public const APPLICANT_TYPES = [
        'new'       => 'New Student',
        'existing'  => 'Existing Student',
        'returning' => 'Returning Student',
    ];

    /* ───────────────────────── Display helpers ───────────────────────── */

    /** code => label, sorted by code, ready for a Select. */
    public static function schoolOptions(): array
    {
        return self::SCHOOLS;
    }

    /** code => "Sutomo 1 — Thamrin" for a Select. */
    public static function campusOptions(?string $schoolCode = null): array
    {
        $out = [];
        foreach (self::CAMPUSES as $code => $row) {
            if ($schoolCode && $row['school'] !== $schoolCode) continue;
            $school = self::SCHOOLS[$row['school']] ?? $row['school'];
            $out[$code] = "{$school} — {$row['name']}";
        }
        return $out;
    }

    /** Unit slug => "TK", "SD", … for a Select (preserves legacy slug). */
    public static function unitOptions(): array
    {
        return array_map(fn ($u) => $u['label'], self::UNITS);
    }

    public static function schoolLabel(?string $code): ?string
    {
        return $code ? (self::SCHOOLS[$code] ?? null) : null;
    }

    public static function campusLabel(?string $code, bool $withSchool = true): ?string
    {
        if (! $code || ! isset(self::CAMPUSES[$code])) return null;
        $row = self::CAMPUSES[$code];
        return $withSchool
            ? (self::SCHOOLS[$row['school']] ?? '') . ' — ' . $row['name']
            : $row['name'];
    }

    public static function campusSchool(?string $code): ?string
    {
        return $code && isset(self::CAMPUSES[$code]) ? self::CAMPUSES[$code]['school'] : null;
    }

    public static function unitLabel(?string $slug): ?string
    {
        return $slug && isset(self::UNITS[$slug]) ? self::UNITS[$slug]['label'] : ($slug ? strtoupper($slug) : null);
    }

    public static function unitCode(?string $slug): ?string
    {
        return $slug && isset(self::UNITS[$slug]) ? self::UNITS[$slug]['code'] : null;
    }

    /** All campus codes belonging to a given school. */
    public static function campusesOfSchool(string $schoolCode): array
    {
        return array_keys(array_filter(self::CAMPUSES, fn ($r) => $r['school'] === $schoolCode));
    }

    /**
     * Grade labels available for a given unit slug, ready for a Select.
     * Returns grade => grade so the same string is the value and label.
     * @return array<string, string>
     */
    public static function gradesForUnit(?string $unit): array
    {
        $grades = match ($unit) {
            'pre_nursery' => ['PN'],
            'playgroup'   => ['PG'],
            'tk'          => ['TK-1', 'TK-2'],
            'sd'          => ['1', '2', '3', '4', '5', '6'],
            'smp'         => ['7', '8', '9'],
            'sma'         => ['10', '11', '12'],
            default       => [],
        };
        return array_combine($grades, $grades);
    }
}
