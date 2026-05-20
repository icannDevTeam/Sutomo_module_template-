<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EbookPack extends Model
{
    protected $guarded = [];

    protected $casts = [
        'items'     => 'array',
        'is_active' => 'bool',
    ];

    public function defaultPlatform(): BelongsTo
    {
        return $this->belongsTo(EbookPlatform::class, 'default_platform_id');
    }
}
