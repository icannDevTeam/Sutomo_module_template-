<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SubstituteOffer extends Model
{
    protected $guarded = [];

    protected $casts = [
        'sent_at'      => 'datetime',
        'expires_at'   => 'datetime',
        'responded_at' => 'datetime',
    ];

    public const STATUSES = [
        'pending'    => 'Awaiting response',
        'interested' => 'Interested',
        'assigned'   => 'Assigned',
        'declined'   => 'Not available',
        'expired'    => 'Expired',
        'cancelled'  => 'Closed',
    ];

    public const STATUS_COLORS = [
        'pending'    => 'warning',
        'interested' => 'info',
        'assigned'   => 'success',
        'declined'   => 'danger',
        'expired'    => 'gray',
        'cancelled'  => 'gray',
    ];

    public const STATUS_ICONS = [
        'pending'    => '',
        'interested' => '',
        'assigned'   => '',
        'declined'   => '',
        'expired'    => '',
        'cancelled'  => '',
    ];

    public function leave(): BelongsTo
    {
        return $this->belongsTo(TeacherLeave::class, 'teacher_leave_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public static function generateToken(): string
    {
        do {
            $token = Str::random(48);
        } while (static::where('token', $token)->exists());

        return $token;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending'
            && (! $this->expires_at || $this->expires_at->isFuture());
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function statusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    public function statusIcon(): string
    {
        return self::STATUS_ICONS[$this->status] ?? '';
    }
}
