<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherCompensation extends Model
{
    protected $table = 'teacher_compensation';
    protected $guarded = [];

    protected $casts = [
        'base_salary'    => 'encrypted',
        'allowances'     => 'encrypted:array',
        'effective_from' => 'date',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function getTotalAttribute(): int
    {
        $base = (int) $this->base_salary;
        $allow = collect($this->allowances ?? [])->sum(fn ($v) => (int) $v);
        return $base + $allow;
    }
}
