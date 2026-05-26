<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\Teacher;
use Illuminate\Support\Collection;

class TeacherActivityFeed
{
    /**
     * Aggregate recent activity for a teacher from:
     *   - audit_logs (target=Teacher:{id} or any string containing teacher id)
     *   - observations, employment events, leaves, certs, documents, duties
     *
     * Returns Collection of array{date, category, verb, detail, actor, notes}, sorted desc.
     */
    public static function for(Teacher $teacher, int $limit = 50): Collection
    {
        $events = collect();

        // 1. AuditLog rows referencing this teacher
        $tag = 'Teacher:'.$teacher->id;
        AuditLog::where('target', $tag)
            ->orWhere('target', 'LIKE', "%/teachers/{$teacher->id}%")
            ->orderByDesc('occurred_at')
            ->limit($limit)
            ->get()
            ->each(function ($a) use (&$events) {
                $events->push([
                    'date'     => $a->occurred_at ?? $a->created_at,
                    'category' => self::categoryFromAction((string) $a->action),
                    'verb'     => $a->action,
                    'detail'   => trim(($a->from_value ? "{$a->from_value} → " : '').($a->to_value ?? '')),
                    'actor'    => $a->user_name ?? 'system',
                    'notes'    => $a->note ?? data_get($a, 'meta.note'),
                ]);
            });

        // 2. Observations
        if (method_exists($teacher, 'observations')) {
            $teacher->observations()->latest('observed_at')->limit($limit)->get()
                ->each(fn ($o) => $events->push([
                    'date'     => $o->observed_at,
                    'category' => 'observation',
                    'verb'     => 'Lesson observation',
                    'detail'   => trim(($o->lesson_subject ?? '').' '.($o->lesson_class_code ?? '')),
                    'actor'    => $o->observer?->name ?? '—',
                    'notes'    => $o->notes ?? null,
                ]));
        }

        // 3. Employment events
        if (method_exists($teacher, 'employmentEvents')) {
            $teacher->employmentEvents()->latest('event_date')->limit($limit)->get()
                ->each(fn ($e) => $events->push([
                    'date'     => $e->event_date,
                    'category' => 'employment',
                    'verb'     => 'Employment: '.($e->event_type),
                    'detail'   => trim(($e->from_value ? "{$e->from_value} → " : '').($e->to_value ?? '')),
                    'actor'    => $e->creator?->name ?? '—',
                    'notes'    => $e->notes ?? null,
                ]));
        }

        // 4. Recent leaves
        if (method_exists($teacher, 'leaves')) {
            $teacher->leaves()->latest('starts_at')->limit($limit)->get()
                ->each(fn ($l) => $events->push([
                    'date'     => $l->starts_at,
                    'category' => 'leave',
                    'verb'     => 'Leave: '.($l->status ?? '—'),
                    'detail'   => $l->reason ?? '',
                    'actor'    => '—',
                    'notes'    => $l->notes ?? null,
                ]));
        }

        // 5. Recent documents
        if (method_exists($teacher, 'documents')) {
            $teacher->documents()->latest('created_at')->limit($limit)->get()
                ->each(fn ($d) => $events->push([
                    'date'     => $d->created_at,
                    'category' => 'document',
                    'verb'     => 'Document uploaded',
                    'detail'   => $d->title ?? $d->type ?? '',
                    'actor'    => '—',
                    'notes'    => $d->notes ?? null,
                ]));
        }

        // 6. Duty assignments
        if (method_exists($teacher, 'dutyAssignments')) {
            $teacher->dutyAssignments()->latest('assigned_at')->limit($limit)->get()
                ->each(fn ($d) => $events->push([
                    'date'     => $d->assigned_at ?? $d->created_at,
                    'category' => 'duty',
                    'verb'     => 'Duty: '.($d->duty_type ?? $d->title ?? 'assignment'),
                    'detail'   => $d->location ?? '',
                    'actor'    => '—',
                    'notes'    => $d->notes ?? null,
                ]));
        }

        return $events
            ->filter(fn ($e) => !empty($e['date']))
            ->sortByDesc(fn ($e) => \Illuminate\Support\Carbon::parse($e['date'])->timestamp)
            ->take($limit)
            ->values();
    }

    protected static function categoryFromAction(string $action): string
    {
        $a = strtolower($action);
        return match (true) {
            str_contains($a, 'leave')                                  => 'leave',
            str_contains($a, 'observ')                                 => 'observation',
            str_contains($a, 'document') || str_contains($a, 'upload') => 'document',
            str_contains($a, 'employ') || str_contains($a, 'contract')
                || str_contains($a, 'promot') || str_contains($a, 'salary')
                || str_contains($a, 'compensation')                    => 'employment',
            str_contains($a, 'duty') || str_contains($a, 'assign')     => 'duty',
            default                                                    => 'default',
        };
    }
}
