<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentCommunication extends Model
{
    protected $guarded = [];

    protected $casts = ['occurred_at' => 'datetime'];

    public const METHODS = [
        'call'      => 'Phone Call',
        'whatsapp'  => 'WhatsApp',
        'email'     => 'Email',
        'in_person' => 'In Person',
    ];

    public const METHOD_ICONS = [
        'call' => 'heroicon-o-phone',
        'whatsapp' => 'heroicon-o-chat-bubble-left-right',
        'email' => 'heroicon-o-envelope',
        'in_person' => 'heroicon-o-user',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
