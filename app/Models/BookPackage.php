<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookPackage extends Model
{
    protected $guarded = [];

    protected $casts = [
        'items'     => 'array',
        'is_active' => 'bool',
        'total'     => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (BookPackage $pkg) {
            $pkg->total = collect($pkg->items ?? [])->sum(function ($row) {
                return (int) ($row['qty'] ?? 0) * (int) ($row['unit_price'] ?? 0);
            });
        });
    }
}
