<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueryLetter extends Model
{
    protected $guarded = [];

    protected $casts = [
        'issued_at'    => 'date',
        'responded_at' => 'datetime',
    ];

    public const STATUSES = [
        'sent'      => 'Awaiting response',
        'responded' => 'Responded',
        'closed'    => 'Closed',
    ];

    public const STATUS_COLORS = [
        'sent'      => 'warning',
        'responded' => 'info',
        'closed'    => 'success',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function statusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }
}
