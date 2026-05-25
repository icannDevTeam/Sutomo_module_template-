<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherClearance extends Model
{
    protected $guarded = [];

    protected $casts = [
        'issued_at'  => 'date',
        'expires_at' => 'date',
    ];

    public const TYPES = [
        'criminal_record'   => 'Criminal Record (SKCK)',
        'child_protection'  => 'Child Protection',
        'medical'           => 'Medical / Health',
        'vaccination'       => 'Vaccination',
        'drug_test'         => 'Drug Test',
        'other'             => 'Other',
    ];

    public const STATUSES = [
        'valid'    => 'Valid',
        'expiring' => 'Expiring',
        'expired'  => 'Expired',
        'pending'  => 'Pending',
    ];

    public const STATUS_COLORS = [
        'valid' => 'success', 'expiring' => 'warning', 'expired' => 'danger', 'pending' => 'gray',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /** Auto-derive status from expires_at if not pending. */
    public function getEffectiveStatusAttribute(): string
    {
        if ($this->status === 'pending') return 'pending';
        if (! $this->expires_at) return $this->status ?: 'valid';
        $days = now()->startOfDay()->diffInDays($this->expires_at, false);
        if ($days < 0)  return 'expired';
        if ($days <= 30) return 'expiring';
        return 'valid';
    }
}
