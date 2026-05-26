<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterOfIntentSchedule extends Model
{
    protected $table = 'letter_of_intent_schedules';

    protected $fillable = [
        'name', 'template_id', 'frequency', 'day_of_month', 'month_of_year', 'run_on',
        'target_scope', 'target_value', 'deadline_days', 'academic_year_offset',
        'active', 'last_run_at', 'next_run_at', 'last_run_count', 'last_run_summary',
    ];

    protected $casts = [
        'target_value'     => 'array',
        'last_run_summary' => 'array',
        'active'           => 'bool',
        'run_on'           => 'date',
        'last_run_at'      => 'datetime',
        'next_run_at'      => 'datetime',
    ];

    public const FREQUENCIES = [
        'yearly'  => 'Yearly (annual cycle)',
        'monthly' => 'Monthly',
        'once'    => 'One-off (specific date)',
    ];

    public const SCOPES = [
        'all_active' => 'All active teachers',
        'by_dept'    => 'By department',
        'by_subject' => 'By subject',
        'by_unit'    => 'By school unit (SD/SMP/SMA)',
        'specific'   => 'Specific teachers',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(LetterOfIntentTemplate::class, 'template_id');
    }

    /**
     * Compute the next run timestamp from frequency settings.
     */
    public function computeNextRun(?Carbon $from = null): ?Carbon
    {
        $from = $from ?? now();

        return match ($this->frequency) {
            'once'    => $this->run_on ? Carbon::parse($this->run_on)->setTime(9, 0) : null,
            'monthly' => $this->nextMonthly($from),
            'yearly'  => $this->nextYearly($from),
            default   => null,
        };
    }

    private function nextMonthly(Carbon $from): ?Carbon
    {
        $day = max(1, min(28, (int) ($this->day_of_month ?? 1)));
        $candidate = $from->copy()->day($day)->setTime(9, 0);
        if ($candidate->lte($from)) {
            $candidate = $candidate->addMonthNoOverflow();
        }
        return $candidate;
    }

    private function nextYearly(Carbon $from): ?Carbon
    {
        $month = max(1, min(12, (int) ($this->month_of_year ?? 4)));
        $day   = max(1, min(28, (int) ($this->day_of_month ?? 1)));
        $candidate = $from->copy()->month($month)->day($day)->setTime(9, 0);
        if ($candidate->lte($from)) {
            $candidate = $candidate->addYear();
        }
        return $candidate;
    }

    public function describe(): string
    {
        return match ($this->frequency) {
            'once'    => 'Once on ' . optional($this->run_on)->format('d M Y'),
            'monthly' => 'Monthly on day ' . ($this->day_of_month ?: '?'),
            'yearly'  => 'Yearly on ' . str_pad((string) ($this->day_of_month ?: 1), 2, '0', STR_PAD_LEFT) . ' '
                . Carbon::create()->month($this->month_of_year ?: 4)->format('F'),
            default => '—',
        };
    }
}
