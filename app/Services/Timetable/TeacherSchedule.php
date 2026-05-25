<?php

namespace App\Services\Timetable;

use App\Models\Teacher;
use App\Models\TeacherLoad;
use App\Models\TimetableDefinition;
use App\Models\TimetableLesson;
use App\Models\TimetableSnapshot;
use Illuminate\Support\Collection;

/**
 * Read-only timetable lookups for a single Teacher.
 * Prefers the most recent published Snapshot; falls back to latest Definition.
 */
class TeacherSchedule
{
    public const DAYS = ['SENIN','SELASA','RABU','KAMIS','JUMAT','SABTU'];
    public const PERIODS = ['I','II','III','IV','V','VI','VII','VIII'];

    /** Resolve the active definition id for lookups. */
    public static function activeDefinitionId(): ?int
    {
        // Prefer a published snapshot's definition (most recently published).
        $snapId = TimetableSnapshot::orderByDesc('published_at')->value('definition_id');
        if ($snapId) {
            return (int) $snapId;
        }
        return (int) TimetableDefinition::orderByDesc('id')->value('id') ?: null;
    }

    public static function for(Teacher $t): self
    {
        return new self($t, self::activeDefinitionId());
    }

    public function __construct(public Teacher $teacher, public ?int $definitionId) {}

    /** All lessons for this teacher in the active definition (Collection of TimetableLesson). */
    public function lessons(): Collection
    {
        if (! $this->definitionId || ! $this->teacher->code) {
            return collect();
        }
        return TimetableLesson::query()
            ->where('definition_id', $this->definitionId)
            ->where('teacher_ref', $this->teacher->code)
            ->get();
    }

    /** Weekly grid: [period => [day => lesson|null]]. */
    public function weekly(): array
    {
        $grid = [];
        foreach (self::PERIODS as $p) {
            foreach (self::DAYS as $d) {
                $grid[$p][$d] = null;
            }
        }
        foreach ($this->lessons() as $l) {
            $grid[$l->period][$l->day] = $l;
        }
        return $grid;
    }

    /** Distinct {class_code, subject} pairs this teacher teaches. */
    public function classesTaught(): Collection
    {
        return $this->lessons()
            ->unique(fn ($l) => $l->class_code . '|' . $l->subject)
            ->values()
            ->map(fn ($l) => ['class_code' => $l->class_code, 'subject' => $l->subject]);
    }

    public function weeklyLessonCount(): int
    {
        return $this->lessons()->count();
    }

    public function weeklyCap(): int
    {
        if (! $this->definitionId || ! $this->teacher->code) {
            return 24;
        }
        return (int) (TeacherLoad::query()
            ->where('definition_id', $this->definitionId)
            ->where('teacher_ref', $this->teacher->code)
            ->value('weekly_cap') ?: 24);
    }

    /** Returns ['count' => int, 'cap' => int, 'pct' => int, 'tone' => 'success|warning|danger'] */
    public function workload(): array
    {
        $count = $this->weeklyLessonCount();
        $cap   = max(1, $this->weeklyCap());
        $pct   = (int) round(($count / $cap) * 100);
        $tone  = $pct >= 90 ? 'danger' : ($pct >= 70 ? 'warning' : 'success');
        return ['count' => $count, 'cap' => $cap, 'pct' => $pct, 'tone' => $tone];
    }

    /** Today's lesson happening NOW (best-effort; relies on day name match, no period start times). */
    public function currentSlot(): ?TimetableLesson
    {
        $dayNames = ['Monday'=>'SENIN','Tuesday'=>'SELASA','Wednesday'=>'RABU','Thursday'=>'KAMIS','Friday'=>'JUMAT','Saturday'=>'SABTU'];
        $today = $dayNames[now()->format('l')] ?? null;
        if (! $today) {
            return null;
        }
        return $this->lessons()->firstWhere('day', $today);
    }

    /** Peer teachers in same department, excluding self. */
    public function departmentPeers(int $limit = 8): Collection
    {
        if (! $this->teacher->dept) {
            return collect();
        }
        return Teacher::query()
            ->where('dept', $this->teacher->dept)
            ->where('id', '!=', $this->teacher->id)
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }
}
