<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherLeave extends Model
{
    protected $guarded = [];

    protected $casts = [
        'starts_at'   => 'date',
        'ends_at'     => 'date',
        'decided_at'  => 'datetime',
    ];

    public const TYPES = [
        'sick'       => 'Sick',
        'emergency'  => 'Emergency',
        'sabbatical' => 'Sabbatical',
        'midday'     => 'Mid-day Absence',
        'prior'      => 'Prior Notice',
    ];

    public const STATUSES = [
        'pending'  => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ];

    public const STATUS_COLORS = [
        'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function substitute(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'substitute_teacher_id');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(SubstituteOffer::class)->orderBy('round')->orderBy('id');
    }

    public function activeOffers(): HasMany
    {
        return $this->offers()->whereNotIn('status', ['cancelled']);
    }

    /**
     * Coverage health: green = substitute assigned OR pending offers exist,
     * amber = no substitute and no active offers but candidates exist,
     * red = no substitute and no candidates available (after consult).
     */
    public function coverageStatus(): string
    {
        if ($this->status !== 'pending') {
            return $this->substitute_teacher_id ? 'covered' : 'none';
        }
        if ($this->substitute_teacher_id) {
            return 'covered';
        }
        $hasAccepted = $this->offers()->where('status', 'accepted')->exists();
        if ($hasAccepted) {
            return 'accepted';
        }
        $hasPending = $this->offers()->where('status', 'pending')->exists();
        if ($hasPending) {
            return 'broadcasting';
        }
        return 'unbroadcast';
    }
}
