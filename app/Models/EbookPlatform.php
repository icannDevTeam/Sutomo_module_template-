<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EbookPlatform extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'bool',
    ];

    public function packs(): HasMany
    {
        return $this->hasMany(EbookPack::class, 'default_platform_id');
    }
}
