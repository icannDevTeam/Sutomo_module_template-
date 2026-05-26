<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Throwable;

class TeacherObservation extends Model
{
    protected $guarded = [];

    protected $casts = [
        'observed_at'         => 'datetime',
        'reviewed_at'         => 'datetime',
        'follow_up_date'      => 'date',
        'dimensions'          => 'array',
        'notes_by_criterion'  => 'array',
    ];

    public const DIMENSIONS = [
        'engagement'      => 'Engagement',
        'clarity'         => 'Clarity',
        'pacing'          => 'Pacing',
        'classroom_mgmt'  => 'Classroom Management',
    ];

    /** Alias for DIMENSIONS (back-compat). New code should prefer criteriaLabels(). */
    public const CRITERIA = self::DIMENSIONS;

    /**
     * Returns labels for currently-active observation criteria, keyed by `key`.
     * Falls back to the DIMENSIONS constant when the observation_criteria table
     * is missing or unreadable (e.g. before migrations run).
     */
    public static function criteriaLabels(): array
    {
        try {
            $rows = ObservationCriterion::query()
                ->where('active', true)
                ->orderBy('order')
                ->get(['key', 'label']);

            if ($rows->isNotEmpty()) {
                return $rows->pluck('label', 'key')->all();
            }
        } catch (Throwable $e) {
            // table not migrated yet or other DB error → fall through
        }

        return self::DIMENSIONS;
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function observer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'observer_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getAverageScoreAttribute(): ?float
    {
        $d = $this->dimensions ?? [];
        $vals = array_filter(array_map(fn ($v) => is_numeric($v) ? (float) $v : null, $d), fn ($v) => $v !== null);
        return count($vals) ? round(array_sum($vals) / count($vals), 1) : null;
    }
}
