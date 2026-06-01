<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherLeave extends Model
{
    protected $guarded = [];

    protected $casts = [
        'starts_at'             => 'date',
        'ends_at'               => 'date',
        'decided_at'            => 'datetime',
        'auto_search_enabled'   => 'bool',
        'auto_search_opened_at' => 'datetime',
        'auto_search_closes_at' => 'datetime',
        'auto_search_closed_at' => 'datetime',
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

    public const AUTO_SEARCH_STATUSES = [
        'disabled'        => 'Disabled',
        'open'            => 'Open',
        'closed_full'     => 'Closed (cap reached)',
        'closed_manual'   => 'Closed by principal',
        'closed_assigned' => 'Closed (substitute assigned)',
        'expired'         => 'Expired',
    ];

    public const AUTO_SEARCH_COLORS = [
        'disabled'        => 'gray',
        'open'            => 'info',
        'closed_full'     => 'warning',
        'closed_manual'   => 'gray',
        'closed_assigned' => 'success',
        'expired'         => 'gray',
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

    public function interestedOffers(): HasMany
    {
        return $this->offers()->where('status', 'interested');
    }

    public function isAutoSearchOpen(): bool
    {
        return $this->auto_search_status === 'open'
            && (! $this->auto_search_closes_at || $this->auto_search_closes_at->isFuture());
    }

    public function autoSearchStatusLabel(): string
    {
        return self::AUTO_SEARCH_STATUSES[$this->auto_search_status] ?? $this->auto_search_status;
    }

    public function autoSearchStatusColor(): string
    {
        return self::AUTO_SEARCH_COLORS[$this->auto_search_status] ?? 'gray';
    }

    /**
     * Coverage health used by the table badge.
     */
    public function coverageStatus(): string
    {
        if ($this->substitute_teacher_id) {
            return 'covered';
        }
        if ($this->status !== 'pending') {
            return 'none';
        }
        $interested = $this->offers()->where('status', 'interested')->count();
        if ($interested > 0) {
            return 'interested';     // principal needs to pick
        }
        if ($this->isAutoSearchOpen()) {
            return 'searching';
        }
        if ($this->auto_search_status !== 'disabled') {
            return 'search_closed';
        }
        return 'unbroadcast';
    }
}
