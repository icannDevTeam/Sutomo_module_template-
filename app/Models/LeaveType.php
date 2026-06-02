<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $guarded = [];

    protected $casts = [
        'affects_quota'       => 'bool',
        'requires_substitute' => 'bool',
        'is_active'           => 'bool',
        'sort_order'          => 'int',
    ];

    public const COLOR_OPTIONS = [
        'gray'    => 'Gray',
        'success' => 'Green',
        'info'    => 'Blue',
        'warning' => 'Amber',
        'danger'  => 'Red',
        'primary' => 'Indigo',
    ];

    public static function options(): array
    {
        return self::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->pluck('label', 'key')
            ->toArray();
    }

    public static function labelFor(?string $key): string
    {
        if (! $key) return 'Leave';
        return self::query()->where('key', $key)->value('label') ?? ucfirst($key);
    }

    public static function affectsQuota(?string $key): bool
    {
        if (! $key) return false;
        return (bool) self::query()->where('key', $key)->value('affects_quota');
    }

    /**
     * Whether a leave of this type requires a substitute teacher to cover
     * the absent teacher's classes. Defaults to TRUE for unknown types.
     */
    public static function requiresSubstitute(?string $key): bool
    {
        if (! $key) return true;
        $val = self::query()->where('key', $key)->value('requires_substitute');
        return $val === null ? true : (bool) $val;
    }
}
