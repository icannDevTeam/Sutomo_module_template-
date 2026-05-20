<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Announcement extends Model
{
    protected $guarded = [];

    protected $casts = [
        'audiences'    => 'array',
        'channels'     => 'array',
        'attachments'  => 'array',
        'pinned'       => 'boolean',
        'scheduled_at' => 'datetime',
        'sent_at'      => 'datetime',
    ];

    public const CATEGORIES = [
        'general'  => 'General',
        'academic' => 'Academic',
        'event'    => 'Event',
        'urgent'   => 'Urgent',
        'reminder' => 'Reminder',
    ];

    public const CATEGORY_COLORS = [
        'general'  => 'gray',
        'academic' => 'info',
        'event'    => 'success',
        'urgent'   => 'danger',
        'reminder' => 'warning',
    ];

    public const AUDIENCES = [
        'all_parents' => 'All Parents',
        'sd'          => 'SD Parents',
        'smp'         => 'SMP Parents',
        'sma'         => 'SMA Parents',
        'staff'       => 'All Staff',
        'teachers'    => 'Teachers',
        'grade_10'    => 'Grade 10',
        'grade_11'    => 'Grade 11',
        'grade_12'    => 'Grade 12',
    ];

    public const CHANNELS = [
        'app'      => 'In-app',
        'email'    => 'Email',
        'whatsapp' => 'WhatsApp',
    ];

    public const STATUSES = [
        'draft'     => 'Draft',
        'scheduled' => 'Scheduled',
        'sent'      => 'Sent',
        'archived'  => 'Archived',
    ];

    public function reads(): HasMany
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    public function audienceLabels(): array
    {
        return collect($this->audiences ?? [])->map(fn ($a) => self::AUDIENCES[$a] ?? $a)->all();
    }

    public function channelLabels(): array
    {
        return collect($this->channels ?? [])->map(fn ($c) => self::CHANNELS[$c] ?? $c)->all();
    }
}
