<?php

namespace App\Services;

use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;

class ClassPlacement
{
    /**
     * Greedy balance students across the given classes minimising gender/religion/ethnicity skew
     * and avoiding same-surname clusters. Respects class capacity.
     *
     * @param Collection<int, Student> $students
     * @param Collection<int, SchoolClass> $classes
     * @return array<int, array{student_id:int, class_id:int, class_name:string}> Proposed assignments.
     */
    public function propose(Collection $students, Collection $classes): array
    {
        $buckets = [];
        foreach ($classes as $c) {
            $buckets[$c->id] = [
                'class'       => $c,
                'count'       => $c->students()->count(),
                'capacity'    => $c->capacity,
                'gender'      => ['M' => 0, 'F' => 0],
                'religion'    => [],
                'ethnicity'   => [],
                'surnames'    => [],
            ];
            foreach ($c->students()->get(['gender','religion','ethnicity','name']) as $s) {
                $buckets[$c->id]['gender'][$s->gender] = ($buckets[$c->id]['gender'][$s->gender] ?? 0) + 1;
                if ($s->religion)  $buckets[$c->id]['religion'][$s->religion]   = ($buckets[$c->id]['religion'][$s->religion] ?? 0) + 1;
                if ($s->ethnicity) $buckets[$c->id]['ethnicity'][$s->ethnicity] = ($buckets[$c->id]['ethnicity'][$s->ethnicity] ?? 0) + 1;
                $sn = $this->surname($s->name);
                $buckets[$c->id]['surnames'][$sn] = ($buckets[$c->id]['surnames'][$sn] ?? 0) + 1;
            }
        }

        $proposals = [];
        foreach ($students as $st) {
            $bestId = null; $bestScore = PHP_INT_MAX;
            foreach ($buckets as $cid => $b) {
                if ($b['count'] >= $b['capacity']) continue;
                $score = $b['count'] * 10
                       + ($b['gender'][$st->gender] ?? 0) * 4
                       + ($b['religion'][$st->religion ?? ''] ?? 0) * 3
                       + ($b['ethnicity'][$st->ethnicity ?? ''] ?? 0) * 3
                       + ($b['surnames'][$this->surname($st->name)] ?? 0) * 8;
                if ($score < $bestScore) { $bestScore = $score; $bestId = $cid; }
            }
            if ($bestId === null) continue;
            $proposals[] = [
                'student_id' => $st->id,
                'student_name' => $st->name,
                'class_id'   => $bestId,
                'class_name' => $buckets[$bestId]['class']->name,
            ];
            $buckets[$bestId]['count']++;
            $buckets[$bestId]['gender'][$st->gender] = ($buckets[$bestId]['gender'][$st->gender] ?? 0) + 1;
            if ($st->religion)  $buckets[$bestId]['religion'][$st->religion]   = ($buckets[$bestId]['religion'][$st->religion] ?? 0) + 1;
            if ($st->ethnicity) $buckets[$bestId]['ethnicity'][$st->ethnicity] = ($buckets[$bestId]['ethnicity'][$st->ethnicity] ?? 0) + 1;
            $sn = $this->surname($st->name);
            $buckets[$bestId]['surnames'][$sn] = ($buckets[$bestId]['surnames'][$sn] ?? 0) + 1;
        }
        return $proposals;
    }

    public function apply(array $proposals): int
    {
        $n = 0;
        foreach ($proposals as $p) {
            Student::where('id', $p['student_id'])->update(['school_class_id' => $p['class_id']]);
            $n++;
        }
        return $n;
    }

    private function surname(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));
        return strtolower(end($parts) ?: '');
    }
}
