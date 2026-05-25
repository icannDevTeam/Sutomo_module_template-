<?php

namespace App\Support;

use App\Models\DutyAssignment;
use App\Models\Teacher;
use App\Models\TeacherDocument;
use App\Models\TeacherLeave;
use Carbon\Carbon;

/**
 * Pure-function warning aggregator for a single Teacher's profile.
 * Returns a list of ['level' => warning|info|danger, 'icon' => emoji, 'text' => string]
 */
class TeacherWarnings
{
    public static function for(Teacher $t): array
    {
        $out = [];
        $today = Carbon::today();

        // Contract expiring
        if ($t->contract_end) {
            $days = $today->diffInDays($t->contract_end, false);
            if ($days < 0) {
                $out[] = ['level'=>'danger','icon'=>'⚠','text'=>'Contract EXPIRED '.abs($days).' day(s) ago'];
            } elseif ($days <= 30) {
                $out[] = ['level'=>'danger','icon'=>'⏰','text'=>"Contract expires in {$days} day(s)"];
            } elseif ($days <= 90) {
                $out[] = ['level'=>'warning','icon'=>'⏳','text'=>"Contract expires in {$days} day(s)"];
            }
        }

        // Documents expiring / expired
        $expiredDocs = TeacherDocument::where('teacher_id', $t->id)
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '<', $today)
            ->count();
        if ($expiredDocs > 0) {
            $out[] = ['level'=>'danger','icon'=>'📄','text'=>"{$expiredDocs} document(s) expired"];
        }
        $soonDocs = TeacherDocument::where('teacher_id', $t->id)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [$today, $today->copy()->addDays(30)])
            ->count();
        if ($soonDocs > 0) {
            $out[] = ['level'=>'warning','icon'=>'📄',"text"=>"{$soonDocs} document(s) expire within 30 days"];
        }

        // Pending documents
        $pendingDocs = TeacherDocument::where('teacher_id', $t->id)->where('status','pending')->count();
        if ($pendingDocs > 0) {
            $out[] = ['level'=>'info','icon'=>'📥','text'=>"{$pendingDocs} document(s) awaiting verification"];
        }

        // Leave/duty conflict: any approved leave overlapping any active duty
        $approvedLeaves = TeacherLeave::where('teacher_id', $t->id)
            ->where('status', 'approved')
            ->where('ends_at', '>=', $today->copy()->subDays(7))
            ->get(['starts_at','ends_at']);
        if ($approvedLeaves->isNotEmpty()) {
            $duties = DutyAssignment::where('teacher_id', $t->id)
                ->whereIn('status', ['assigned','accepted'])
                ->get(['starts_at','ends_at']);
            foreach ($approvedLeaves as $lv) {
                foreach ($duties as $du) {
                    if (! $du->starts_at || ! $du->ends_at) continue;
                    if ($du->starts_at->between($lv->starts_at, $lv->ends_at)
                        || $du->ends_at->between($lv->starts_at, $lv->ends_at)) {
                        $out[] = ['level'=>'danger','icon'=>'❗','text'=>'Duty conflicts with approved leave window'];
                        break 2;
                    }
                }
            }
        }

        // Pending leaves
        $pendingLeaves = TeacherLeave::where('teacher_id', $t->id)->where('status','pending')->count();
        if ($pendingLeaves > 0) {
            $out[] = ['level'=>'info','icon'=>'🏖','text'=>"{$pendingLeaves} leave request(s) awaiting decision"];
        }

        return $out;
    }
}
