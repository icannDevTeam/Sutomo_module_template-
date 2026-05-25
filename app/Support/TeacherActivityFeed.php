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
     *   - observations, employment events, leaves, certs, documents (creation timestamps)
     *
     * Returns Collection of array{date, icon, color, verb, detail, actor}, sorted desc.
     */
    public static function for(Teacher $teacher, int $limit = 20): Collection
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
                    'date'   => $a->occurred_at ?? $a->created_at,
                    'icon'   => '📝',
                    'color'  => '#6366f1',
                    'verb'   => $a->action,
                    'detail' => $a->note ?? trim(($a->from_value ? "{$a->from_value} → " : '').($a->to_value ?? '')),
                    'actor'  => $a->user_name ?? 'system',
                ]);
            });

        // 2. Observations
        $teacher->observations()->latest('observed_at')->limit($limit)->get()
            ->each(fn ($o) => $events->push([
                'date'   => $o->observed_at,
                'icon'   => '👁',
                'color'  => '#0ea5e9',
                'verb'   => 'Lesson observation',
                'detail' => trim(($o->lesson_subject ?? '').' '.($o->lesson_class_code ?? '')),
                'actor'  => $o->observer?->name ?? '—',
            ]));

        // 3. Employment events
        $teacher->employmentEvents()->latest('event_date')->limit($limit)->get()
            ->each(fn ($e) => $events->push([
                'date'   => $e->event_date,
                'icon'   => '💼',
                'color'  => '#16a34a',
                'verb'   => 'Employment: '.($e->event_type),
                'detail' => trim(($e->from_value ? "{$e->from_value} → " : '').($e->to_value ?? '')),
                'actor'  => $e->creator?->name ?? '—',
            ]));

        // 4. Recent leaves
        $teacher->leaves()->latest('starts_at')->limit($limit)->get()
            ->each(fn ($l) => $events->push([
                'date'   => $l->starts_at,
                'icon'   => '🌴',
                'color'  => '#a855f7',
                'verb'   => 'Leave: '.($l->status ?? '—'),
                'detail' => $l->reason ?? '',
                'actor'  => '—',
            ]));

        // 5. Recent documents
        $teacher->documents()->latest('created_at')->limit($limit)->get()
            ->each(fn ($d) => $events->push([
                'date'   => $d->created_at,
                'icon'   => '📄',
                'color'  => '#f59e0b',
                'verb'   => 'Document uploaded',
                'detail' => $d->title ?? $d->type ?? '',
                'actor'  => '—',
            ]));

        return $events
            ->filter(fn ($e) => !empty($e['date']))
            ->sortByDesc('date')
            ->take($limit)
            ->values();
    }
}
