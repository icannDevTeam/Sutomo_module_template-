<?php

namespace App\Services\Timetable;

use App\Models\TimetableLesson;
use App\Models\TimetableSnapshot;
use Illuminate\Support\Facades\DB;

/**
 * Freezes the current state of a timetable definition into an immutable snapshot
 * and flips the definition status to "published".
 */
class PublishService
{
    public function publish(int $definitionId, ?string $label = null, ?string $by = null): TimetableSnapshot
    {
        return DB::transaction(function () use ($definitionId, $label, $by) {
            $lessons = TimetableLesson::where('definition_id', $definitionId)->get();
            $conflicts = $lessons->filter(fn ($l) => ! empty($l->conflicts))->count();

            $snap = TimetableSnapshot::create([
                'definition_id'  => $definitionId,
                'label'          => $label ?: ('Published ' . now()->format('Y-m-d H:i')),
                'published_by'   => $by,
                'published_at'   => now(),
                'lesson_count'   => $lessons->count(),
                'conflict_count' => $conflicts,
                'payload'        => $lessons->map(fn ($l) => [
                    'teacher_ref' => $l->teacher_ref,
                    'class_code'  => $l->class_code,
                    'subject'     => $l->subject,
                    'session'     => $l->session,
                    'period'      => $l->period,
                    'day'         => $l->day,
                    'room'        => $l->room,
                    'locked'      => $l->locked,
                ])->values()->all(),
            ]);

            DB::table('timetable_definitions')
                ->where('id', $definitionId)
                ->update(['status' => 'published', 'updated_at' => now()]);

            return $snap;
        });
    }
}
