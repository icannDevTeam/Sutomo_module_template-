<?php

namespace App\Support;

use App\Models\Teacher;
use Illuminate\Support\Carbon;

/**
 * Templated teacher summary. LLM wiring deferred — for now returns a deterministic
 * paragraph from existing data.
 */
class TeacherSummary
{
    public static function generate(Teacher $t): string
    {
        $parts = [];

        $title = $t->title ? (Teacher::TITLES[$t->title] ?? $t->title) : 'Teacher';
        $tenureYears = $t->joined_at ? Carbon::parse($t->joined_at)->diffInYears(now()) : null;

        $parts[] = "{$t->name} ({$title})"
            .($tenureYears !== null ? " has been with us for {$tenureYears} year(s)" : '')
            .($t->dept ? " in the {$t->dept} department" : '')
            .'.';

        if ($t->subject) {
            $parts[] = "Primary subject: {$t->subject}.";
        }

        $homeroomCount = $t->homeroomClasses()->count();
        if ($homeroomCount) {
            $parts[] = "Homeroom teacher for {$homeroomCount} class(es).";
        }

        $certCount = $t->formalCertifications()->count();
        $clearOk   = $t->clearances()->where('status', 'valid')->count();
        if ($certCount || $clearOk) {
            $parts[] = "Holds {$certCount} formal certification(s) and {$clearOk} valid clearance(s).";
        }

        $obsCount = $t->observations()->count();
        $goalsActive = $t->goals()->whereIn('status', ['on_track', 'at_risk'])->count();
        if ($obsCount || $goalsActive) {
            $parts[] = "Recent activity: {$obsCount} observation(s), {$goalsActive} active goal(s).";
        }

        if ($t->contract_end) {
            $days = (int) now()->diffInDays($t->contract_end, false);
            if ($days >= 0 && $days <= 90) {
                $parts[] = "⚠ Contract ends in {$days} day(s).";
            }
        }

        return implode(' ', $parts);
    }
}
