<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherContract extends Model
{
    protected $guarded = [];

    protected $casts = [
        'starts_at'                          => 'date',
        'ends_at'                            => 'date',
        'probation_starts_at'                => 'date',
        'probation_ends_at'                  => 'date',
        'probation_decision_at'              => 'datetime',
        'commitment_fee_refund_triggered_at' => 'datetime',
        'signed_at'                          => 'datetime',
        'renewal_window_starts_at'           => 'date',
        'renewal_notified_at'                => 'datetime',
        'renewal_form_submitted_at'          => 'datetime',
        'submitted_to_yayasan_at'            => 'datetime',
        'yayasan_response_at'                => 'datetime',
        'sk_issued_at'                       => 'datetime',
        'agreement_signed_at'                => 'datetime',
        'buku_induk_recorded_at'             => 'datetime',
        'continuation_completed_at'          => 'datetime',
    ];

    public const TYPES = [
        'pkwt_1'  => 'PKWT-I (Probation)',
        'pkwt_2'  => 'PKWT-II',
        'pkwt_3'  => 'PKWT-III',
        'guru_sk' => 'Guru SK (Permanent)',
    ];

    public const TYPE_COLORS = [
        'pkwt_1'  => 'warning',
        'pkwt_2'  => 'info',
        'pkwt_3'  => 'primary',
        'guru_sk' => 'success',
    ];

    public const STATUSES = [
        'active'      => 'Active',
        'renewed'     => 'Renewed',
        'expired'     => 'Expired',
        'terminated'  => 'Terminated',
        'not_renewed' => 'Not Renewed',
        'resigned'    => 'Resigned',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function lettersOfIntent(): HasMany
    {
        return $this->hasMany(LetterOfIntent::class);
    }

    // ---------- Scopes ----------

    public function scopeByTier(Builder $q, string $type): Builder
    {
        return $q->where('type', $type);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', 'active');
    }

    /** Teachers currently inside their probation window (PKWT-I). */
    public function scopeProbationActive(Builder $q): Builder
    {
        $today = now()->toDateString();
        return $q->where('type', 'pkwt_1')
            ->whereNotNull('probation_starts_at')
            ->whereNotNull('probation_ends_at')
            ->whereDate('probation_starts_at', '<=', $today)
            ->whereDate('probation_ends_at', '>=', $today);
    }

    /** Inside their 3-month-before-end renewal window (PKWT-I/II/III). */
    public function scopeInRenewalWindow(Builder $q): Builder
    {
        $today = now()->toDateString();
        return $q->whereIn('type', ['pkwt_1', 'pkwt_2', 'pkwt_3'])
            ->where('status', 'active')
            ->whereNotNull('renewal_window_starts_at')
            ->whereDate('renewal_window_starts_at', '<=', $today)
            ->whereDate('ends_at', '>=', $today);
    }

    // ---------- Helpers ----------

    public function isProbation(): bool
    {
        return $this->type === 'pkwt_1';
    }

    public function isPermanent(): bool
    {
        return $this->type === 'guru_sk';
    }

    public function daysUntilEnd(): ?int
    {
        if (! $this->ends_at) {
            return null;
        }
        return (int) Carbon::today()->diffInDays($this->ends_at, false);
    }

    /**
     * Default renewal window = 3 months before contract end.
     * Caller may override per-contract by setting renewal_window_starts_at directly.
     */
    public static function defaultRenewalWindowStart(?Carbon $endsAt): ?Carbon
    {
        return $endsAt?->copy()->subMonths(3);
    }

    /**
     * Default probation window = 3 months from contract start (universal rule).
     */
    public static function defaultProbationEnd(?Carbon $startsAt): ?Carbon
    {
        return $startsAt?->copy()->addMonths(3);
    }

    /**
     * Spawn the next-tier contract after this one.
     * Marks self as renewed. Returns the new contract.
     *
     * @param string $nextType pkwt_2 | pkwt_3 | guru_sk
     */
    public function renewAs(string $nextType, ?int $renewalWindowMonths = null): self
    {
        $renewalWindowMonths ??= (int) (\App\Models\ObservationSetting::current()->renewal_window_months ?? 3);

        $startsAt = $this->ends_at?->copy()->addDay() ?? Carbon::today();
        $endsAt = $nextType === 'guru_sk' ? null : $startsAt->copy()->addYear()->subDay();
        $renewalStart = $endsAt ? $endsAt->copy()->subMonths($renewalWindowMonths) : null;

        $new = self::create([
            'teacher_id'                => $this->teacher_id,
            'type'                      => $nextType,
            'academic_year'             => \App\Models\DutyAssignment::academicYearFor($startsAt),
            'starts_at'                 => $startsAt->toDateString(),
            'ends_at'                   => $endsAt?->toDateString(),
            'renewal_window_starts_at'  => $renewalStart?->toDateString(),
            'status'                    => 'active',
        ]);

        $this->update(['status' => 'renewed']);

        // Cascade teacher status.
        $this->teacher?->update(['status' => $nextType]);

        return $new;
    }
}
