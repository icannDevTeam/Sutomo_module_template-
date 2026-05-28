<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessageThread extends Model
{
    protected $guarded = [];

    protected $casts = [
        'last_message_at' => 'datetime',
        'unread_count'    => 'integer',
        'is_group'        => 'bool',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(ThreadMessage::class, 'thread_id')->orderBy('sent_at');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ThreadParticipant::class, 'thread_id');
    }

    public function lastMessage()
    {
        return $this->messages()->latest('sent_at')->first();
    }

    public function displayName(): string
    {
        return $this->is_group
            ? ($this->group_name ?: 'Group chat')
            : $this->partner_name;
    }

    public function displayInitials(): string
    {
        if ($this->is_group) {
            $name = $this->group_name ?: 'Group';
            $parts = preg_split('/\s+/', trim($name));
            $first = mb_substr($parts[0] ?? '', 0, 1);
            $second = mb_substr($parts[1] ?? '', 0, 1);
            return mb_strtoupper($first . $second) ?: 'GC';
        }
        return $this->partner_initials ?? mb_strtoupper(mb_substr($this->partner_name, 0, 2));
    }
}
