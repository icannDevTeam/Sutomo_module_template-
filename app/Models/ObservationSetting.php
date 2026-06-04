<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObservationSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'probation_watch_enabled'         => 'boolean',
        'probation_min_supervisions'      => 'integer',
        'probation_min_peer_observations' => 'integer',
        'probation_decision_due_days'     => 'integer',
        'renewal_window_months'           => 'integer',
    ];

    /**
     * Always returns the singleton row. Falls back to in-memory defaults
     * if the table is missing (pre-migration).
     */
    public static function current(): self
    {
        try {
            $row = self::query()->find(1);
            if ($row) {
                return $row;
            }
        } catch (\Throwable $e) {
            // table not migrated — fall through to in-memory default
        }

        return new self([
            'id'                              => 1,
            'probation_watch_enabled'         => true,
            'probation_min_supervisions'      => 4,
            'probation_min_peer_observations' => 2,
            'probation_decision_due_days'     => 14,
            'renewal_window_months'           => 3,
        ]);
    }
}
