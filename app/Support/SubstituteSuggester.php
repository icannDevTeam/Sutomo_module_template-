<?php

namespace App\Support;

use App\Models\DutyAssignment;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\TeacherLeave;
use Illuminate\Support\Collection;

/**
 * Builds a ranked, availability-annotated list of substitute candidates for a leave window.
 *
 * Each returned row:
 *   - teacher              : Teacher model
 *   - tier                 : 1..4
 *   - tier_label           : string
 *   - rank_in_tier         : int (1-based)
 *   - availability         : 'free' | 'has_duty' | 'unavailable'
 *   - availability_color   : 'success' | 'warning' | 'danger'
 *   - availability_label   : string
 *   - conflict_reasons     : string[]  (human-readable)
 *   - is_assignable        : bool
 *   - phone                : ?string
 *   - email                : ?string
 *   - wa_link              : ?string
 */
class SubstituteSuggester
{
    public const TIER_VIP              = 0;
    public const TIER_PREFERRED        = 1;
    public const TIER_GLOBAL_PREFERRED = 2;
    public const TIER_SUBJECT          = 3;
    public const TIER_DEPT             = 4;
    public const TIER_CAMPUS           = 5;

    public const TIER_LABELS = [
        self::TIER_VIP              => 'VIP',
        self::TIER_PREFERRED        => 'Pinned for this teacher',
        self::TIER_GLOBAL_PREFERRED => 'Globally preferred',
        self::TIER_SUBJECT          => 'Subject match',
        self::TIER_DEPT             => 'Dept match',
        self::TIER_CAMPUS           => 'Same campus',
    ];

    public static function for(TeacherLeave $leave, int $limit = 10): Collection
    {
        $teacher = $leave->teacher;
        if (! $teacher) {
            return collect();
        }

        $start = $leave->starts_at;
        $end   = $leave->ends_at ?? $leave->starts_at;
        if (! $start || ! $end) {
            return collect();
        }

        // ---- Tier 0: VIP (substitution_category = 'vip', same campus) ----
        $vip = Teacher::query()
            ->where('id', '!=', $teacher->id)
            ->where('status', '!=', 'alumni')
            ->where('substitution_category', 'vip')
            ->when($teacher->campus, fn ($q) => $q->where('campus', $teacher->campus))
            ->orderBy('name')
            ->get();

        $seen = $vip->pluck('id')->all();
        $seen[] = $teacher->id;

        // ---- Tier 1: Preferred (pinned for this teacher, pivot order) ----
        $preferred = $teacher->preferredSubstitutes()
            ->where('teachers.id', '!=', $teacher->id)
            ->where('status', '!=', 'alumni')
            ->where('substitution_category', '!=', 'blocked')
            ->whereNotIn('teachers.id', $seen)
            ->get();

        $seen = array_merge($seen, $preferred->pluck('id')->all());

        // ---- Pool for tiers 1.5-4: same campus, not alumni, not blocked, not already picked ----
        $poolBase = Teacher::query()
            ->whereNotIn('id', $seen)
            ->where('status', '!=', 'alumni')
            ->where('substitution_category', '!=', 'blocked')
            ->when($teacher->campus, fn ($q) => $q->where('campus', $teacher->campus));

        // ---- Tier 1.5: Globally Preferred ----
        $globalPreferred = (clone $poolBase)
            ->where('substitution_category', 'preferred')
            ->orderBy('name')
            ->get();

        $seen = array_merge($seen, $globalPreferred->pluck('id')->all());

        $subject = (clone $poolBase)
            ->whereNotIn('id', $seen)
            ->when($teacher->subject, fn ($q) => $q->where('subject', $teacher->subject))
            ->when(! $teacher->subject, fn ($q) => $q->whereRaw('1 = 0'))
            ->orderBy('name')
            ->get();

        $seen = array_merge($seen, $subject->pluck('id')->all());

        $dept = (clone $poolBase)
            ->whereNotIn('id', $seen)
            ->when($teacher->dept, fn ($q) => $q->where('dept', $teacher->dept))
            ->when(! $teacher->dept, fn ($q) => $q->whereRaw('1 = 0'))
            ->orderBy('name')
            ->get();

        $seen = array_merge($seen, $dept->pluck('id')->all());

        $campusOnly = (clone $poolBase)
            ->whereNotIn('id', $seen)
            ->orderBy('name')
            ->get();

        // ---- Assemble + annotate (restricted teachers sink to bottom of each tier) ----
        $rows = collect();
        self::pushTier($rows, self::sinkRestricted($vip),              self::TIER_VIP,              $start, $end, $leave->id);
        self::pushTier($rows, self::sinkRestricted($preferred),        self::TIER_PREFERRED,        $start, $end, $leave->id);
        self::pushTier($rows, self::sinkRestricted($globalPreferred),  self::TIER_GLOBAL_PREFERRED, $start, $end, $leave->id);
        self::pushTier($rows, self::sinkRestricted($subject),          self::TIER_SUBJECT,          $start, $end, $leave->id);
        self::pushTier($rows, self::sinkRestricted($dept),             self::TIER_DEPT,             $start, $end, $leave->id);
        self::pushTier($rows, self::sinkRestricted($campusOnly),       self::TIER_CAMPUS,           $start, $end, $leave->id);

        return $rows->take($limit)->values();
    }

    /**
     * Stable sort: non-restricted teachers first, restricted at the bottom.
     */
    protected static function sinkRestricted(Collection $teachers): Collection
    {
        return $teachers->sortBy(
            fn (Teacher $t) => ($t->substitution_category ?? 'standard') === 'restricted' ? 1 : 0,
            SORT_REGULAR,
        )->values();
    }

    protected static function pushTier(Collection $rows, Collection $teachers, int $tier, $start, $end, ?int $excludeLeaveId): void
    {
        $i = 1;
        foreach ($teachers as $t) {
            $rows->push(self::annotate($t, $tier, $i++, $start, $end, $excludeLeaveId));
        }
    }

    protected static function annotate(Teacher $t, int $tier, int $rankInTier, $start, $end, ?int $excludeLeaveId): array
    {
        $conflicts = self::detectConflicts($t, $start, $end, $excludeLeaveId);

        $hasHardConflict = (bool) array_filter($conflicts, fn ($c) => $c['hard']);
        $hasSoftConflict = (bool) array_filter($conflicts, fn ($c) => ! $c['hard']);

        if ($hasHardConflict) {
            $availability       = 'unavailable';
            $availabilityColor  = 'danger';
            $availabilityLabel  = 'Unavailable';
            $isAssignable       = false;
        } elseif ($hasSoftConflict) {
            $availability       = 'has_duty';
            $availabilityColor  = 'warning';
            $availabilityLabel  = 'Has duty';
            $isAssignable       = true;
        } else {
            $availability       = 'free';
            $availabilityColor  = 'success';
            $availabilityLabel  = 'Free';
            $isAssignable       = true;
        }

        return [
            'teacher'            => $t,
            'tier'               => $tier,
            'tier_label'         => self::TIER_LABELS[$tier],
            'rank_in_tier'       => $rankInTier,
            'availability'       => $availability,
            'availability_color' => $availabilityColor,
            'availability_label' => $availabilityLabel,
            'conflict_reasons'   => array_column($conflicts, 'reason'),
            'is_assignable'      => $isAssignable,
            'phone'              => $t->phone,
            'email'              => $t->email,
            'wa_link'            => self::whatsappLink($t->phone),
            'category'           => $t->substitution_category ?? 'standard',
            'category_label'     => Teacher::SUBSTITUTION_CATEGORIES[$t->substitution_category ?? 'standard'] ?? 'Standard',
            'category_color'     => Teacher::SUBSTITUTION_CATEGORY_COLORS[$t->substitution_category ?? 'standard'] ?? 'gray',
        ];
    }

    /**
     * @return array<int, array{hard: bool, reason: string}>
     */
    protected static function detectConflicts(Teacher $t, $start, $end, ?int $excludeLeaveId): array
    {
        $out = [];

        // Overlapping approved leave (own)
        $ownLeaves = TeacherLeave::query()
            ->where('teacher_id', $t->id)
            ->where('status', 'approved')
            ->where('starts_at', '<=', $end)
            ->where('ends_at',   '>=', $start)
            ->get(['starts_at', 'ends_at']);

        foreach ($ownLeaves as $l) {
            $out[] = [
                'hard'   => true,
                'reason' => 'On approved leave ' . $l->starts_at->format('d M') . ' – ' . $l->ends_at->format('d M'),
            ];
        }

        // Already assigned as substitute on an approved leave overlapping window
        $coverConflicts = TeacherLeave::query()
            ->where('substitute_teacher_id', $t->id)
            ->where('status', 'approved')
            ->when($excludeLeaveId, fn ($q) => $q->where('id', '!=', $excludeLeaveId))
            ->where('starts_at', '<=', $end)
            ->where('ends_at',   '>=', $start)
            ->with('teacher:id,name')
            ->get();

        foreach ($coverConflicts as $l) {
            $out[] = [
                'hard'   => true,
                'reason' => 'Already covering ' . ($l->teacher?->name ?? 'another teacher')
                            . ' (' . $l->starts_at->format('d M') . '–' . $l->ends_at->format('d M') . ')',
            ];
        }

        // Attendance flag inside the window
        $absentDay = TeacherAttendance::query()
            ->where('teacher_id', $t->id)
            ->whereIn('status', ['leave', 'absent'])
            ->whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->first();

        if ($absentDay) {
            $out[] = [
                'hard'   => true,
                'reason' => 'Recorded ' . ucfirst($absentDay->status) . ' on ' . $absentDay->date->format('d M'),
            ];
        }

        // Active duty assignments overlapping window — soft conflict
        $duties = DutyAssignment::query()
            ->where('teacher_id', $t->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->where('starts_at', '<=', $end->copy()->endOfDay())
            ->where(function ($q) use ($start) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>=', $start->copy()->startOfDay());
            })
            ->get(['title', 'starts_at']);

        foreach ($duties as $d) {
            $out[] = [
                'hard'   => false,
                'reason' => 'Duty: ' . $d->title . ' (' . $d->starts_at->format('d M') . ')',
            ];
        }

        return $out;
    }

    public static function whatsappLink(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }
        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits === '' || $digits === null) {
            return null;
        }
        // Indonesian normalization: leading 0 -> 62
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }
        return 'https://wa.me/' . $digits;
    }
}
