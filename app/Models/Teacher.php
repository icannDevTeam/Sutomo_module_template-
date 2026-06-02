<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class Teacher extends Model
{
    protected $guarded = [];

    protected $casts = [
        'dob'             => 'date',
        'joined_at'       => 'date',
        'contract_end'    => 'date',
        'last_review'     => 'date',
        'certifications'  => 'array',
        'languages'       => 'array',
        'awards'          => 'array',
        'initiatives'     => 'array',
        'rating'          => 'float',
        'bank_account_no' => 'encrypted',
        'tax_id_npwp'     => 'encrypted',
        'promotion_readiness_set_at'    => 'datetime',
        'substitution_category_set_at'  => 'datetime',
    ];

    public const SUBSTITUTION_CATEGORIES = [
        'vip'        => 'VIP',
        'preferred'  => 'Globally Preferred',
        'standard'   => 'Standard',
        'restricted' => 'Restricted',
        'blocked'    => 'Blocked',
    ];

    public const SUBSTITUTION_CATEGORY_COLORS = [
        'vip'        => 'success',
        'preferred'  => 'info',
        'standard'   => 'gray',
        'restricted' => 'warning',
        'blocked'    => 'danger',
    ];

    public const SUBSTITUTION_CATEGORY_DESCRIPTIONS = [
        'vip'        => 'Always invited first (subject to conflicts).',
        'preferred'  => 'Boosted above generic matches.',
        'standard'   => 'Default — ranked normally by subject/dept/campus.',
        'restricted' => 'Sinks to the bottom of any tier — invited only when no one else is available.',
        'blocked'    => 'Never invited and never appears in the candidate list.',
    ];

    public function isSubstitutionBlocked(): bool
    {
        return ($this->substitution_category ?? 'standard') === 'blocked';
    }

    public function substitutionCategorySetter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'substitution_category_set_by');
    }

    public const STATUSES = [
        'permanent' => 'Permanent',
        'contract'  => 'Contract',
        'probation' => 'Probation',
        'opl'       => 'OPL',
        'leave'     => 'On Leave',
        'alumni'    => 'Alumni',
    ];

    public const TITLES = [
        'teacher'             => 'Teacher',
        'senior_teacher'      => 'Senior Teacher',
        'subject_coordinator' => 'Subject Coordinator',
        'unit_head'           => 'Unit Head',
        'vice_principal'      => 'Vice Principal',
        'principal'           => 'Principal',
    ];

    public const TITLE_COLORS = [
        'teacher'             => 'gray',
        'senior_teacher'      => 'info',
        'subject_coordinator' => 'warning',
        'unit_head'           => 'success',
        'vice_principal'      => 'danger',
        'principal'           => 'danger',
    ];

    public const PROMOTION_LEVELS = [
        'ready'      => 'Ready',
        'developing' => 'Developing',
        'not_ready'  => 'Not ready',
    ];

    public const PROMOTION_COLORS = [
        'ready'      => 'success',
        'developing' => 'warning',
        'not_ready'  => 'danger',
    ];

    public function promotionSetter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'promotion_readiness_set_by');
    }

    /**
     * Return promotion readiness: manual override wins, else computed.
     * @return array{level:string,label:string,color:string,source:string,reason:string,setBy:?string,setAt:?\Illuminate\Support\Carbon}
     */
    public function promotionReadiness(): array
    {
        $manual = $this->promotion_readiness;
        if ($manual && isset(self::PROMOTION_LEVELS[$manual])) {
            return [
                'level'  => $manual,
                'label'  => self::PROMOTION_LEVELS[$manual],
                'color'  => self::PROMOTION_COLORS[$manual],
                'source' => 'manual',
                'reason' => (string) ($this->promotion_readiness_note ?? ''),
                'setBy'  => optional($this->promotionSetter)->name,
                'setAt'  => $this->promotion_readiness_set_at
                    ? Carbon::parse($this->promotion_readiness_set_at) : null,
            ];
        }

        $attn   = $this->attendanceRate(90);
        $obs    = $this->observationAverage(3);
        $open   = $this->openQueryLettersCount();
        $tenure = $this->tenureYears();
        $review = $this->last_review ? Carbon::parse($this->last_review) : null;
        $reviewOk = $review && $review->gt(now()->subMonths(12));

        $reasons = [];
        if ($tenure < 1)                $reasons[] = 'tenure < 1y';
        if ($attn !== null && $attn < 75) $reasons[] = 'attendance < 75%';
        if ($obs  !== null && $obs  < 2.5) $reasons[] = 'observation < 2.5';
        if ($open > 0)                  $reasons[] = $open . ' open query letter' . ($open > 1 ? 's' : '');

        if (! empty($reasons)) {
            $level = 'not_ready';
        } elseif (
            $tenure >= 3
            && ($attn === null || $attn >= 90)
            && ($obs  === null || $obs  >= 3.5)
            && $reviewOk
        ) {
            $level = 'ready';
        } else {
            $level = 'developing';
            if ($tenure < 3)                $reasons[] = 'tenure < 3y';
            if ($attn !== null && $attn < 90) $reasons[] = 'attendance ' . $attn . '%';
            if ($obs  !== null && $obs  < 3.5) $reasons[] = 'obs avg ' . number_format($obs, 1);
            if (! $reviewOk)                $reasons[] = 'review > 12 mo';
        }

        return [
            'level'  => $level,
            'label'  => self::PROMOTION_LEVELS[$level],
            'color'  => self::PROMOTION_COLORS[$level],
            'source' => 'auto',
            'reason' => $reasons ? implode(' · ', $reasons) : 'meets all criteria',
            'setBy'  => null,
            'setAt'  => null,
        ];
    }

    /** Attendance % over the last N days. Returns null if no data. */
    public function attendanceRate(int $days = 90): ?int
    {
        try {
            $start = now()->subDays($days);
            $rows = $this->attendance()->where('date', '>=', $start)->get();
            $present = $rows->where('status', 'present')->count();
            $base    = $present
                     + $rows->where('status', 'late')->count()
                     + $rows->where('status', 'absent')->count();
            return $base ? (int) round(($present / $base) * 100) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Average observation score over last N observations. Returns null if none. */
    public function observationAverage(int $last = 3): ?float
    {
        try {
            $vals = $this->observations()
                ->orderByDesc('observed_at')
                ->limit($last)
                ->get()
                ->map(fn ($o) => $o->average_score)
                ->filter(fn ($v) => $v !== null && (float) $v > 0)
                ->map(fn ($v) => (float) $v);
            return $vals->isEmpty() ? null : round($vals->avg(), 2);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function openQueryLettersCount(): int
    {
        try {
            return (int) $this->queryLetters()
                ->whereIn('status', ['sent', 'responded'])
                ->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /** Average progress % across active (non-completed) goals. */
    public function goalsProgressAverage(): ?int
    {
        try {
            $vals = $this->goals()->pluck('progress')->filter(fn ($v) => $v !== null);
            return $vals->isEmpty() ? null : (int) round($vals->avg());
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function tenureYears(): float
    {
        if (! $this->joined_at) return 0.0;
        return round(Carbon::parse($this->joined_at)->floatDiffInYears(now()), 1);
    }

    public function isDueForReview(): bool
    {
        if (! $this->last_review) return true;
        return Carbon::parse($this->last_review)->lt(now()->subMonths(12));
    }

    public function isContractEndingWithin(int $days = 90): bool
    {
        if (! $this->contract_end) return false;
        $end = Carbon::parse($this->contract_end);
        return $end->gte(now()->startOfDay()) && $end->lte(now()->addDays($days));
    }

    public function isOnLeaveOn(\Carbon\CarbonInterface|\Illuminate\Support\Carbon $date): bool
    {
        try {
            return $this->leaves()
                ->where('status', 'approved')
                ->where('starts_at', '<=', $date)
                ->where('ends_at', '>=', $date)
                ->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function isOnLeaveBetween($start, $end): bool
    {
        try {
            return $this->leaves()
                ->where('status', 'approved')
                ->where('starts_at', '<=', $end)
                ->where('ends_at', '>=', $start)
                ->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function hasPinnedNotes(): bool
    {
        try {
            return $this->notes()->where('pinned', true)->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function hasMissingRequiredDocs(): bool
    {
        try {
            return $this->clearances()
                ->whereIn('status', ['pending', 'expired', 'missing'])
                ->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(TeacherLeave::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(TeacherNote::class)
            ->orderByDesc('pinned')
            ->orderByDesc('created_at');
    }

    public function queryLetters(): HasMany
    {
        return $this->hasMany(QueryLetter::class)->orderByDesc('issued_at');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TeacherDocument::class);
    }

    public function voluntaryRequests(): HasMany
    {
        return $this->hasMany(VoluntaryRequest::class);
    }

    public function duties(): HasMany
    {
        return $this->hasMany(DutyAssignment::class);
    }

    public function trainings(): HasMany
    {
        return $this->hasMany(TeacherTraining::class);
    }

    public function childrenStudents(): HasMany
    {
        return $this->hasMany(Student::class, 'parent_teacher_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(TeacherTag::class);
    }

    public function homeroomClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'homeroom_teacher_id');
    }

    // ===== Phase 3: credentials =====
    public function formalCertifications(): HasMany
    {
        return $this->hasMany(TeacherCertification::class);
    }

    public function clearances(): HasMany
    {
        return $this->hasMany(TeacherClearance::class);
    }

    public function cpdHoursForYear(?string $academicYear = null): int
    {
        $q = $this->trainings();
        if ($academicYear) {
            // crude AY filter: 'YYYY/YYYY' → year between starts_on year span
            [$y1, $y2] = array_pad(explode('/', $academicYear), 2, null);
            if ($y1) {
                $start = Carbon::create((int) $y1, 7, 1);
                $end   = $y2 ? Carbon::create((int) $y2, 6, 30) : (clone $start)->addYear();
                $q->whereBetween('starts_on', [$start, $end]);
            }
        }
        return (int) $q->sum('hours_certified');
    }

    // ===== Phase 4: substitutes / mentor / attendance / employment =====
    public function preferredSubstitutes(): BelongsToMany
    {
        return $this->belongsToMany(
                self::class,
                'teacher_substitutes',
                'teacher_id',
                'substitute_teacher_id'
            )
            ->withPivot(['rank','note'])
            ->withTimestamps()
            ->orderBy('teacher_substitutes.rank');
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'mentor_id');
    }

    public function mentees(): HasMany
    {
        return $this->hasMany(self::class, 'mentor_id');
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(TeacherAttendance::class);
    }

    public function substituteOffers(): HasMany
    {
        return $this->hasMany(SubstituteOffer::class);
    }

    /**
     * Days used (approved leaves only) within an academic year.
     * Only counts leave types where LeaveType.affects_quota = true.
     */
    public function leaveDaysUsed(?int $year = null): int
    {
        $year = $year ?? now()->year;
        $start = "{$year}-01-01";
        $end   = "{$year}-12-31";

        $countingTypes = LeaveType::query()
            ->where('affects_quota', true)
            ->pluck('key')
            ->all();

        if (empty($countingTypes)) {
            return 0;
        }

        return (int) $this->leaves()
            ->whereIn('type', $countingTypes)
            ->where('status', 'approved')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('starts_at', [$start, $end])
                  ->orWhereBetween('ends_at', [$start, $end]);
            })
            ->get()
            ->sum(fn ($l) => max(1, $l->starts_at->diffInDays($l->ends_at) + 1));
    }

    public function quotaFor(): int
    {
        return (int) ($this->quota ?? 12);
    }

    public function employmentEvents(): HasMany
    {
        return $this->hasMany(TeacherEmploymentEvent::class);
    }

    /** Suggest substitutes: not self, not already pinned, active, same campus, ranked by subject/dept match. */
    public static function suggestSubstitutes(Teacher $teacher, int $limit = 5)
    {
        $pinned = $teacher->preferredSubstitutes()->pluck('teachers.id')->all();
        $exclude = array_merge($pinned, [$teacher->id]);

        return self::query()
            ->whereNotIn('id', $exclude)
            ->where('status', '!=', 'alumni')
            ->when($teacher->campus, fn ($q) => $q->where('campus', $teacher->campus))
            ->selectRaw('*, (CASE WHEN subject = ? THEN 3 WHEN dept = ? THEN 2 ELSE 1 END) AS match_score', [$teacher->subject, $teacher->dept])
            ->orderByDesc('match_score')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    // ===== Phase 6: growth & feedback =====
    public function observations(): HasMany
    {
        return $this->hasMany(TeacherObservation::class);
    }

    public function goals(): HasMany
    {
        return $this->hasMany(TeacherGoal::class);
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(TeacherJournalEntry::class);
    }

    public function parentCommunications(): HasMany
    {
        return $this->hasMany(ParentCommunication::class);
    }

    public function parentCommSummary(): array
    {
        $q = $this->parentCommunications();
        $count = (int) (clone $q)->count();
        $last = (clone $q)->orderByDesc('occurred_at')->value('occurred_at');
        return ['count' => $count, 'last' => $last];
    }

    public function compensations(): HasMany
    {
        return $this->hasMany(TeacherCompensation::class)->orderByDesc('effective_from');
    }

    public function currentCompensation(): ?TeacherCompensation
    {
        return $this->compensations()
            ->where('effective_from', '<=', now())
            ->orderByDesc('effective_from')->first();
    }

    public function sensitiveApprovalRequests(): HasMany
    {
        return $this->hasMany(SensitiveApprovalRequest::class, 'target_teacher_id');
    }

    /** Avatar URL accessor — falls back to a tiny initials data-uri-free placeholder path. */
    protected function avatarUrl(): Attribute
    {
        return Attribute::get(function () {
            return $this->avatar_path
                ? Storage::disk('public')->url($this->avatar_path)
                : null;
        });
    }

    /** Normalize phone to E.164 (+62…) on save. Accepts 08… / 628… / +628… / spaces / dashes. */
    protected function phone(): Attribute
    {
        return Attribute::set(fn ($value) => self::normalizePhone($value));
    }

    protected function emergencyContactPhone(): Attribute
    {
        return Attribute::set(fn ($value) => self::normalizePhone($value));
    }

    public static function normalizePhone(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        // Strip everything except digits and a leading +
        $clean = preg_replace('/[^\d+]/', '', $value);
        if ($clean === '' || $clean === '+') {
            return null;
        }
        if (str_starts_with($clean, '+')) {
            return $clean;
        }
        if (str_starts_with($clean, '0')) {
            return '+62' . substr($clean, 1);
        }
        if (str_starts_with($clean, '62')) {
            return '+' . $clean;
        }
        return '+62' . $clean;
    }
}


