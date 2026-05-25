<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DutyAssignment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'starts_at'    => 'datetime',
        'ends_at'      => 'datetime',
        'responded_at' => 'datetime',
        'days_of_week' => 'array',
    ];

    public const STATUSES = [
        'pending'   => 'Pending',
        'accepted'  => 'Accepted',
        'declined'  => 'Declined',
        'completed' => 'Completed',
    ];

    public const STATUS_COLORS = [
        'pending' => 'warning', 'accepted' => 'info',
        'declined' => 'danger', 'completed' => 'success',
    ];

    public const RECURRENCES = [
        'once'   => 'One-time',
        'weekly' => 'Weekly (recurring)',
    ];

    public const DAYS = [
        1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu',
        5 => 'Fri', 6 => 'Sat', 7 => 'Sun',
    ];

    public const DAYS_LONG = [
        1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday',
        5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Best-effort academic year string (e.g. "2025/2026") derived from a date.
     * Indonesian school year typically starts in July.
     */
    public static function academicYearFor(?\DateTimeInterface $date = null): string
    {
        $date = $date ? \Carbon\Carbon::instance($date) : \Carbon\Carbon::now();
        $year = (int) $date->format('Y');
        $month = (int) $date->format('n');
        return $month >= 7 ? "{$year}/" . ($year + 1) : ($year - 1) . "/{$year}";
    }

    public static function currentAcademicYear(): string
    {
        return self::academicYearFor();
    }

    public static function availableAcademicYears(): array
    {
        $current = self::currentAcademicYear();
        $fromDb = self::query()
            ->whereNotNull('academic_year')
            ->distinct()
            ->pluck('academic_year')
            ->filter()
            ->toArray();

        $all = array_unique(array_merge([$current], $fromDb));
        rsort($all);
        return array_combine($all, $all);
    }
}
