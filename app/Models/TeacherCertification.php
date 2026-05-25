<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherCertification extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_government_approved' => 'bool',
        'issued_at'  => 'date',
        'expires_at' => 'date',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function getExpiryStatusAttribute(): string
    {
        if (! $this->expires_at) return 'valid';
        $days = now()->startOfDay()->diffInDays($this->expires_at, false);
        if ($days < 0)  return 'expired';
        if ($days <= 30) return 'expiring_30';
        if ($days <= 60) return 'expiring_60';
        if ($days <= 90) return 'expiring_90';
        return 'valid';
    }
}
