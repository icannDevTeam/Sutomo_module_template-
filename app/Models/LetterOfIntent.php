<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterOfIntent extends Model
{
    protected $table = 'letters_of_intent';

    protected $fillable = [
        'teacher_id', 'principal_id', 'academic_year', 'position',
        'body', 'notes', 'deadline_at',
    ];

    protected $casts = [
        'sent_at'     => 'datetime',
        'signed_at'   => 'datetime',
        'deadline_at' => 'datetime',
    ];

    public const STATUSES = [
        'draft'    => 'Draft',
        'sent'     => 'Sent',
        'signed'   => 'Signed',
        'declined' => 'Declined',
        'expired'  => 'Expired',
    ];

    public const STATUS_COLORS = [
        'draft'    => 'gray',
        'sent'     => 'info',
        'signed'   => 'success',
        'declined' => 'danger',
        'expired'  => 'warning',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function principal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'principal_id');
    }
}
